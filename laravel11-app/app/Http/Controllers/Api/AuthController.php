<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documentos;
use App\Models\Noticias;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, true)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        $request->session()->regenerate();

        return $this->me($request);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json($this->serializeUser($request->user()));
    }

    public function updateMe(Request $request)
    {
        $user = $request->user();
        $persona = $user->persona;

        $data = $request->validate([
            'Telefono' => ['nullable', 'string', 'max:30'],
            'TelefonoEmergencia' => ['nullable', 'string', 'max:30'],
            'Direccion' => ['nullable', 'string', 'max:255'],
            'Comuna' => ['nullable', 'string', 'max:255'],
        ]);

        if ($persona) {
            $persona->update($data);
        }

        return response()->json($this->serializeUser($user->fresh()));
    }

    private function serializeUser(User $user): array
    {
        $persona = $user->persona;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->getFilamentAvatarUrl(),
            'rol' => $user->role?->Rol,
            'cargo' => $persona?->cargo?->Cargo,
            'esAdministrador' => $user->isRole('Administrador'),
            'esTesorero' => $user->isCargo('Tesorero'),
            'permisos' => [
                'gestionarNoticias' => $user->can('create', Noticias::class),
                'gestionarDocumentos' => $user->can('create', Documentos::class),
            ],
            'persona' => $persona ? [
                'id' => $persona->id,
                'Rut' => $persona->Rut,
                'Nombre' => $persona->Nombre,
                'Telefono' => $persona->Telefono,
                'TelefonoEmergencia' => $persona->TelefonoEmergencia,
                'Direccion' => $persona->Direccion,
                'Comuna' => $persona->Comuna,
                'FechaReclutamiento' => optional($persona->FechaReclutamiento)->format('Y-m-d'),
                'Foto' => $persona->Foto,
                'Activo' => (bool) $persona->Activo,
                'estado' => $persona->estado?->Estado,
                'cargo' => $persona->cargo?->Cargo,
            ] : null,
        ];
    }
}
