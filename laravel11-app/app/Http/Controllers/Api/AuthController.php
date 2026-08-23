<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Documentos;
use App\Models\Noticias;
use App\Models\Persona;
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

    private function serializeUser(User $user): array
    {
        $persona = $user->persona;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'idRole' => $user->idRole,
            'avatar' => $user->getFilamentAvatarUrl(),
            'rol' => $user->role?->Rol,
            'cargo' => $persona?->cargo?->Cargo,
            'esAdministrador' => $user->isRole('Administrador'),
            'esTesorero' => $user->isCargo('Tesorero'),
            'permisos' => [
                'gestionarNoticias' => $user->can('create', Noticias::class),
                'gestionarDocumentos' => $user->can('create', Documentos::class),
                'gestionarPersonas' => $user->can('update', Persona::class),
                'verPersonas' => $user->can('viewAny', Persona::class),
                'solicitarLicenciaParaOtros' => $user->isRole('Administrador') || $user->isCargo(['Director', 'Capitan', 'Capitán']),
            ],
            'persona' => $persona ? [
                'id' => $persona->id,
                'Rut' => $persona->Rut,
                'Nombre' => $persona->Nombre,
                'Telefono' => $persona->Telefono,
                'TelefonoEmergencia' => $persona->TelefonoEmergencia,
                'FechaNacimiento' => optional($persona->FechaNacimiento)->format('Y-m-d'),
                'Nacionalidad' => $persona->Nacionalidad,
                'Direccion' => $persona->Direccion,
                'Comuna' => $persona->Comuna,
                'NivelEstudio' => $persona->NivelEstudio,
                'Ocupacion' => $persona->Ocupacion,
                'LugarOcupacion' => $persona->LugarOcupacion,
                'EstadoCivil' => $persona->EstadoCivil,
                'GrupoSanguineo' => $persona->GrupoSanguineo,
                'TallaZapatos' => $persona->TallaZapatos,
                'TallaPantalon' => $persona->TallaPantalon,
                'TallaCamisa' => $persona->TallaCamisa,
                'TallaChaqueta' => $persona->TallaChaqueta,
                'TallaSombrero' => $persona->TallaSombrero,
                'Observaciones' => $persona->Observaciones,
                'FechaReclutamiento' => optional($persona->FechaReclutamiento)->format('Y-m-d'),
                'Foto' => $persona->Foto ? asset('storage/'.$persona->Foto) : null,
                'idCargo' => $persona->idCargo,
                'idEstado' => $persona->idEstado,
                'Activo' => (bool) $persona->Activo,
                'estado' => $persona->estado?->Estado,
                'cargo' => $persona->cargo?->Cargo,
            ] : null,
        ];
    }
}
