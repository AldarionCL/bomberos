<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}
