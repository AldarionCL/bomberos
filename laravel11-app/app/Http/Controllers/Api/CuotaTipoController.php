<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CuotaTipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuotaTipoController extends Controller
{
    private function serialize(CuotaTipo $tipo): array
    {
        return [
            'id' => $tipo->id,
            'nombre' => $tipo->nombre,
            'descripcion' => $tipo->descripcion,
            'tipoCobro' => $tipo->tipoCobro,
            'activo' => (bool) $tipo->activo,
            'esMensual' => (bool) $tipo->es_mensual,
            'esCuotaInscripcion' => (bool) $tipo->es_cuota_inscripcion,
        ];
    }

    public function index(Request $request)
    {
        $query = CuotaTipo::query()->orderBy('nombre');

        if ($request->boolean('soloAsignables')) {
            $query->where('es_mensual', false)->where('activo', true);
        }

        return response()->json($query->get()->map(fn ($t) => $this->serialize($t)));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:cuotas_tipo,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipoCobro' => ['required', 'in:CUOTA,PAGO'],
            'activo' => ['boolean'],
        ]);

        $tipo = CuotaTipo::create([...$data, 'es_mensual' => false]);

        return response()->json($this->serialize($tipo), 201);
    }

    public function update(Request $request, CuotaTipo $cuotaTipo)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:cuotas_tipo,nombre,'.$cuotaTipo->id],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'tipoCobro' => ['required', 'in:CUOTA,PAGO'],
            'activo' => ['boolean'],
            'esMensual' => ['boolean'],
            'esCuotaInscripcion' => ['boolean'],
        ]);

        DB::transaction(function () use ($cuotaTipo, $data) {
            if ($data['esMensual'] ?? false) {
                CuotaTipo::where('id', '!=', $cuotaTipo->id)->update(['es_mensual' => false]);
            }
            if ($data['esCuotaInscripcion'] ?? false) {
                CuotaTipo::where('id', '!=', $cuotaTipo->id)->update(['es_cuota_inscripcion' => false]);
            }

            $cuotaTipo->update([
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'tipoCobro' => $data['tipoCobro'],
                'activo' => $data['activo'] ?? $cuotaTipo->activo,
                'es_mensual' => $data['esMensual'] ?? $cuotaTipo->es_mensual,
                'es_cuota_inscripcion' => $data['esCuotaInscripcion'] ?? $cuotaTipo->es_cuota_inscripcion,
            ]);
        });

        return response()->json($this->serialize($cuotaTipo->fresh()));
    }

    public function destroy(Request $request, CuotaTipo $cuotaTipo)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);
        abort_if($cuotaTipo->es_mensual, 422, 'No se puede eliminar el tipo marcado como cuota mensual.');
        abort_if($cuotaTipo->es_cuota_inscripcion, 422, 'No se puede eliminar el tipo marcado como cuota de inscripción.');
        abort_if($cuotaTipo->cuotas()->exists(), 422, 'No se puede eliminar: ya tiene cuotas asociadas. Desactívelo en su lugar.');

        $cuotaTipo->delete();

        return response()->noContent();
    }
}
