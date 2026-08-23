<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documentos;
use App\Models\DocumentosTipo;
use App\Models\Noticias;
use App\Models\Persona;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{
    private function serialize(Documentos $documento): array
    {
        $extension = $documento->Path ? strtolower(pathinfo($documento->Path, PATHINFO_EXTENSION)) : null;

        return [
            'id' => $documento->id,
            'nombre' => $documento->Nombre,
            'nroDocumento' => $documento->NroDocumento,
            'descripcion' => $documento->Descripcion,
            'extension' => $extension,
            'url' => $documento->Path ? asset('storage/'.$documento->Path) : null,
            'tipo' => $documento->tipo?->Tipo,
            'clasificacion' => $documento->tipo?->Clasificacion,
            'asociadoA' => $documento->asociado ? [
                'id' => $documento->asociado->id,
                'nombre' => $documento->asociado->name,
            ] : null,
            'creadoEn' => optional($documento->created_at)->format('Y-m-d'),
        ];
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Documentos::query()->with(['tipo', 'asociado'])->orderByDesc('created_at');

        if ($asociadoA = $request->query('asociadoA')) {
            $query->where('AsociadoA', $asociadoA);
            $puedeVerPrivados = $user->can('update', Persona::class) || (int) $asociadoA === $user->id;
            if (! $puedeVerPrivados) {
                $query->whereHas('tipo', fn ($q) => $q->where('Clasificacion', 'publico'));
            }
        } elseif (! $user->isRole('Administrador')) {
            $query->whereHas('tipo', fn ($q) => $q->where('Clasificacion', 'publico'));
        }

        if ($tipo = $request->query('idTipo')) {
            $query->where('TipoDocumento', $tipo);
        }

        if ($busqueda = $request->query('busqueda')) {
            $query->where('Nombre', 'like', "%{$busqueda}%");
        }

        $documentos = $query->paginate($request->integer('porPagina', 20));

        return response()->json([
            'data' => collect($documentos->items())->map(fn ($d) => $this->serialize($d)),
            'meta' => [
                'current_page' => $documentos->currentPage(),
                'last_page' => $documentos->lastPage(),
                'total' => $documentos->total(),
            ],
        ]);
    }

    public function show(Documentos $documento)
    {
        return response()->json($this->serialize($documento->load(['tipo', 'asociado'])));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Documentos::class);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nroDocumento' => ['nullable', 'string', 'max:255'],
            'idTipo' => ['required', 'exists:documentos_tipos,id'],
            'descripcion' => ['nullable', 'string'],
            'archivo' => ['required', 'file', 'max:20480'],
            'asociadoA' => ['nullable', 'exists:users,id'],
            'publicarNoticia' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('archivo')->store('documentos', 'public');

        $documento = Documentos::create([
            'Nombre' => $data['nombre'],
            'NroDocumento' => $data['nroDocumento'] ?? null,
            'TipoDocumento' => $data['idTipo'],
            'Descripcion' => $data['descripcion'] ?? null,
            'Path' => $path,
            'AsociadoA' => $data['asociadoA'] ?? null,
        ]);

        if ($data['publicarNoticia'] ?? false) {
            $tipoNombre = DocumentosTipo::find($data['idTipo'])?->Tipo;
            $url = Storage::disk('public')->url($path);

            Noticias::create([
                'Titulo' => 'Nuevo documento publicado',
                'Subtitulo' => $tipoNombre,
                'Contenido' => "Se ha publicado un nuevo documento: {$data['nombre']}.",
                'idDocumento' => $documento->id,
                'Estado' => 1,
                'FechaPublicacion' => Carbon::today()->format('Y-m-d'),
                'createdBy' => $request->user()->id,
            ]);
        }

        $destinatarios = isset($data['asociadoA']) ? User::where('id', $data['asociadoA'])->get() : User::all();
        Notification::make()
            ->title('Nuevo documento publicado')
            ->body('Se ha publicado un nuevo documento: "'.$data['nombre'].'"')
            ->success()
            ->sendToDatabase($destinatarios);

        return response()->json($this->serialize($documento->load(['tipo', 'asociado'])), 201);
    }

    public function update(Request $request, Documentos $documento)
    {
        $this->authorize('update', Documentos::class);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nroDocumento' => ['nullable', 'string', 'max:255'],
            'idTipo' => ['required', 'exists:documentos_tipos,id'],
            'descripcion' => ['nullable', 'string'],
            'archivo' => ['nullable', 'file', 'max:20480'],
            'asociadoA' => ['nullable', 'exists:users,id'],
        ]);

        $path = $documento->Path;
        if ($request->hasFile('archivo')) {
            $path = $request->file('archivo')->store('documentos', 'public');
        }

        $documento->update([
            'Nombre' => $data['nombre'],
            'NroDocumento' => $data['nroDocumento'] ?? null,
            'TipoDocumento' => $data['idTipo'],
            'Descripcion' => $data['descripcion'] ?? null,
            'Path' => $path,
            'AsociadoA' => $data['asociadoA'] ?? null,
        ]);

        return response()->json($this->serialize($documento->fresh(['tipo', 'asociado'])));
    }

    public function destroy(Request $request, Documentos $documento)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $documento->delete();

        return response()->noContent();
    }
}
