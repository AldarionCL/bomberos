<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CuotaTipo;
use App\Models\PrecioCuotas;
use App\Services\CuotaMensualService;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    public function __construct(private CuotaMensualService $cuotaMensualService)
    {
    }

    public function showCuotaMensual(Request $request)
    {
        $tipo = $this->cuotaMensualService->tipoMensual();

        return response()->json([
            'idCuotaTipo' => $tipo?->id,
            'nombre' => $tipo?->nombre,
            'monto' => $this->cuotaMensualService->montoVigente(),
        ]);
    }

    public function updateCuotaMensual(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:1'],
        ]);

        $tipo = $this->cuotaMensualService->tipoMensual();
        abort_unless($tipo, 422, 'No hay un tipo de cuota marcado como mensual. Configúrelo primero en Tipos de Cuota.');

        PrecioCuotas::create([
            'TipoVoluntario' => 'miembro',
            'TipoCuota' => $tipo->nombre,
            'Monto' => $data['monto'],
        ]);

        return response()->json([
            'idCuotaTipo' => $tipo->id,
            'nombre' => $tipo->nombre,
            'monto' => $this->cuotaMensualService->montoVigente(),
        ]);
    }

    public function showCuotaInscripcion(Request $request)
    {
        $tipo = CuotaTipo::where('es_cuota_inscripcion', true)->first();

        return response()->json([
            'idCuotaTipo' => $tipo?->id,
            'nombre' => $tipo?->nombre,
            'monto' => $tipo ? PrecioCuotas::vigentePara($tipo->nombre) : 0,
        ]);
    }

    public function updateCuotaInscripcion(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'monto' => ['required', 'numeric', 'min:1'],
        ]);

        $tipo = CuotaTipo::where('es_cuota_inscripcion', true)->first();
        abort_unless($tipo, 422, 'No hay un tipo de cuota marcado como cuota de inscripción. Configúrelo primero en Tipos de Cuota.');

        PrecioCuotas::create([
            'TipoVoluntario' => 'miembro',
            'TipoCuota' => $tipo->nombre,
            'Monto' => $data['monto'],
        ]);

        return response()->json([
            'idCuotaTipo' => $tipo->id,
            'nombre' => $tipo->nombre,
            'monto' => PrecioCuotas::vigentePara($tipo->nombre),
        ]);
    }
}
