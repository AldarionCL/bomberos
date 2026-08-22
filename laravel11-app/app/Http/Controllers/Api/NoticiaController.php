<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documentos;
use App\Models\Noticias;
use Illuminate\Http\Request;

class NoticiaController extends Controller
{
    private function serialize(Noticias $noticia): array
    {
        $documento = $noticia->documento;

        return [
            'id' => $noticia->id,
            'titulo' => $noticia->Titulo,
            'subtitulo' => $noticia->Subtitulo,
            'contenido' => $noticia->Contenido,
            'imagen' => $noticia->Imagen ? asset('storage/'.$noticia->Imagen) : null,
            'estado' => $noticia->Estado,
            'fechaPublicacion' => optional($noticia->FechaPublicacion)->format('Y-m-d'),
            'fechaExpiracion' => optional($noticia->FechaExpiracion)->format('Y-m-d'),
            'autor' => $noticia->user?->name,
            'creadoEn' => optional($noticia->created_at)->format('Y-m-d H:i'),
            'documento' => $documento ? [
                'id' => $documento->id,
                'nombre' => $documento->Nombre,
                'tipo' => $documento->tipo?->Tipo,
                'extension' => $documento->Path ? strtolower(pathinfo($documento->Path, PATHINFO_EXTENSION)) : null,
                'url' => $documento->Path ? asset('storage/'.$documento->Path) : null,
            ] : null,
        ];
    }

    public function index(Request $request)
    {
        $query = Noticias::query()->with(['user', 'documento.tipo'])->orderByDesc('created_at');

        if (! $request->boolean('todas') || ! $request->user()->can('viewAny', Noticias::class)) {
            $query->where('Estado', 1);
        }

        $noticias = $query->paginate($request->integer('porPagina', 10));

        return response()->json([
            'data' => collect($noticias->items())->map(fn ($n) => $this->serialize($n)),
            'meta' => [
                'current_page' => $noticias->currentPage(),
                'last_page' => $noticias->lastPage(),
                'total' => $noticias->total(),
            ],
        ]);
    }

    public function show(Noticias $noticia)
    {
        return response()->json($this->serialize($noticia->load(['user', 'documento.tipo'])));
    }

    /**
     * Crea o actualiza, si corresponde, el Documento adjunto a una noticia a
     * partir de los campos `documentoNombre`/`documentoTipo`/`documentoDescripcion`/
     * `documentoArchivo` del request, y devuelve el id a guardar en `idDocumento`.
     */
    private function sincronizarDocumentoAdjunto(Request $request, ?Documentos $existente): ?int
    {
        $tieneDatosNuevos = $request->filled('documentoNombre') || $request->hasFile('documentoArchivo');

        if (! $tieneDatosNuevos) {
            return $existente?->id;
        }

        $data = $request->validate([
            'documentoNombre' => ['required', 'string', 'max:255'],
            'documentoTipo' => ['required', 'exists:documentos_tipos,id'],
            'documentoDescripcion' => ['nullable', 'string'],
            'documentoArchivo' => [$existente ? 'nullable' : 'required', 'file', 'max:20480'],
        ]);

        $path = $existente?->Path;
        if ($request->hasFile('documentoArchivo')) {
            $path = $request->file('documentoArchivo')->store('documentos', 'public');
        }

        $atributos = [
            'Nombre' => $data['documentoNombre'],
            'TipoDocumento' => $data['documentoTipo'],
            'Descripcion' => $data['documentoDescripcion'] ?? null,
            'Path' => $path,
        ];

        if ($existente) {
            $existente->update($atributos);

            return $existente->id;
        }

        return Documentos::create($atributos)->id;
    }

    public function store(Request $request)
    {
        $this->authorize('create', Noticias::class);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'contenido' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'max:5120'],
            'estado' => ['required', 'integer', 'in:1,2,3'],
            'fechaPublicacion' => ['nullable', 'date'],
            'fechaExpiracion' => ['nullable', 'date'],
        ]);

        $imagenPath = $request->hasFile('imagen')
            ? $request->file('imagen')->store('noticias', 'public')
            : null;

        $idDocumento = $this->sincronizarDocumentoAdjunto($request, null);

        $noticia = Noticias::create([
            'Titulo' => $data['titulo'],
            'Subtitulo' => $data['subtitulo'] ?? null,
            'Contenido' => $data['contenido'] ?? null,
            'Imagen' => $imagenPath,
            'idDocumento' => $idDocumento,
            'Estado' => $data['estado'],
            'FechaPublicacion' => $data['fechaPublicacion'] ?? now(),
            'FechaExpiracion' => $data['fechaExpiracion'] ?? null,
            'createdBy' => $request->user()->id,
        ]);

        return response()->json($this->serialize($noticia->load('documento.tipo')), 201);
    }

    public function update(Request $request, Noticias $noticia)
    {
        $this->authorize('update', Noticias::class);

        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'subtitulo' => ['nullable', 'string', 'max:255'],
            'contenido' => ['nullable', 'string'],
            'imagen' => ['nullable', 'image', 'max:5120'],
            'estado' => ['required', 'integer', 'in:1,2,3'],
            'fechaPublicacion' => ['nullable', 'date'],
            'fechaExpiracion' => ['nullable', 'date'],
        ]);

        if ($request->hasFile('imagen')) {
            $data['Imagen'] = $request->file('imagen')->store('noticias', 'public');
        }

        $idDocumento = $this->sincronizarDocumentoAdjunto($request, $noticia->documento);

        $noticia->update([
            'Titulo' => $data['titulo'],
            'Subtitulo' => $data['subtitulo'] ?? null,
            'Contenido' => $data['contenido'] ?? null,
            'Imagen' => $data['Imagen'] ?? $noticia->Imagen,
            'idDocumento' => $idDocumento,
            'Estado' => $data['estado'],
            'FechaPublicacion' => $data['fechaPublicacion'] ?? $noticia->FechaPublicacion,
            'FechaExpiracion' => $data['fechaExpiracion'] ?? null,
        ]);

        return response()->json($this->serialize($noticia->fresh(['documento.tipo'])));
    }

    public function destroy(Request $request, Noticias $noticia)
    {
        $this->authorize('update', Noticias::class);

        $noticia->delete();

        return response()->noContent();
    }
}
