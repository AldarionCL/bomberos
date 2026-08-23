<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Services\CuotaMensualService;
use Illuminate\Http\Request;

class CarnetController extends Controller
{
    public function __construct(private CuotaMensualService $cuotaMensualService)
    {
    }

    public function mio(Request $request)
    {
        $persona = $request->user()->persona;
        abort_unless($persona, 422, 'Tu usuario no tiene un perfil de socio asociado.');

        return response()->json([
            'nombre' => $request->user()->name,
            'rut' => $persona->Rut,
            'cargo' => $persona->cargo?->Cargo,
            'foto' => $request->user()->getFilamentAvatarUrl(),
            'estadoSocio' => $persona->estado?->Estado,
            'activo' => (bool) $persona->Activo,
            'cuotaAlDia' => $this->cuotaMensualService->estaAlDia($persona),
            'token' => $persona->tokenCarnet(),
            'urlVerificacion' => url('/v2/verificar/'.$persona->tokenCarnet()),
        ]);
    }

    public function verificar(Request $request, string $token)
    {
        $persona = Persona::where('token', $token)->first();
        abort_unless($persona, 404);

        return response()->json([
            'nombre' => $persona->user?->name,
            'rut' => $persona->Rut,
            'cargo' => $persona->cargo?->Cargo,
            'foto' => $persona->user?->getFilamentAvatarUrl(),
            'estadoSocio' => $persona->estado?->Estado,
            'activo' => (bool) $persona->Activo,
            'cuotaAlDia' => $this->cuotaMensualService->estaAlDia($persona),
            'verificadoEn' => now()->format('Y-m-d H:i'),
        ]);
    }
}
