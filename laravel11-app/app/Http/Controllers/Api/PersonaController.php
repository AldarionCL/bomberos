<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonaController extends Controller
{
    private function esTesoreria(Request $request): bool
    {
        $user = $request->user();

        return $user->isRole('Administrador') || $user->isCargo('Tesorero');
    }

    private function serialize(User $user): array
    {
        $persona = $user->persona;

        return [
            'idUsuario' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'idRole' => $user->idRole,
            'rol' => $user->role?->Rol,
            'avatar' => $user->getFilamentAvatarUrl(),
            'persona' => $persona ? [
                'id' => $persona->id,
                'Rut' => $persona->Rut,
                'Telefono' => $persona->Telefono,
                'TelefonoEmergencia' => $persona->TelefonoEmergencia,
                'Direccion' => $persona->Direccion,
                'Comuna' => $persona->Comuna,
                'FechaReclutamiento' => optional($persona->FechaReclutamiento)->format('Y-m-d'),
                'idCargo' => $persona->idCargo,
                'cargo' => $persona->cargo?->Cargo,
                'idEstado' => $persona->idEstado,
                'estado' => $persona->estado?->Estado,
                'Activo' => (bool) $persona->Activo,
            ] : null,
        ];
    }

    public function index(Request $request)
    {
        abort_unless($this->esTesoreria($request), 403);

        $query = User::query()->with(['persona.cargo', 'persona.estado', 'role']);

        if ($busqueda = $request->query('busqueda')) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$busqueda}%")->orWhere('email', 'like', "%{$busqueda}%"));
        }

        if ($idCargo = $request->query('idCargo')) {
            $query->whereHas('persona', fn ($q) => $q->where('idCargo', $idCargo));
        }

        $usuarios = $query->orderBy('name')->paginate($request->integer('porPagina', 20));

        return response()->json([
            'data' => collect($usuarios->items())->map(fn ($u) => $this->serialize($u)),
            'meta' => [
                'current_page' => $usuarios->currentPage(),
                'last_page' => $usuarios->lastPage(),
                'total' => $usuarios->total(),
            ],
        ]);
    }

    public function show(Request $request, Persona $persona)
    {
        $user = $request->user();
        abort_unless($this->esTesoreria($request) || $user->id === $persona->idUsuario, 403);

        return response()->json($this->serialize($persona->user));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'idRole' => ['required', 'exists:user_roles,id'],
            'Rut' => ['required', 'string', 'unique:personas,Rut'],
            'Telefono' => ['nullable', 'string', 'max:30'],
            'idCargo' => ['required', 'exists:persona_cargos,id'],
            'FechaReclutamiento' => ['required', 'date'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'idRole' => $data['idRole'],
        ]);

        Persona::create([
            'idUsuario' => $user->id,
            'Rut' => $data['Rut'],
            'Telefono' => $data['Telefono'] ?? null,
            'idCargo' => $data['idCargo'],
            'idEstado' => 1,
            'FechaReclutamiento' => $data['FechaReclutamiento'],
            'TipoVoluntario' => 'miembro',
            'Activo' => true,
        ]);

        return response()->json($this->serialize($user->fresh()), 201);
    }

    public function update(Request $request, Persona $persona)
    {
        $user = $request->user();
        $esAdmin = $user->isRole('Administrador');
        abort_unless($esAdmin || $user->id === $persona->idUsuario, 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'Telefono' => ['nullable', 'string', 'max:30'],
            'TelefonoEmergencia' => ['nullable', 'string', 'max:30'],
            'Direccion' => ['nullable', 'string', 'max:255'],
            'Comuna' => ['nullable', 'string', 'max:255'],
            'idCargo' => ['sometimes', 'exists:persona_cargos,id'],
            'idEstado' => ['sometimes', 'exists:persona_estados,id'],
            'Activo' => ['sometimes', 'boolean'],
        ]);

        if ($esAdmin) {
            $persona->update($data);
            if (isset($data['name'])) {
                $persona->user->update(['name' => $data['name']]);
            }
        } else {
            $persona->update([
                'Telefono' => $data['Telefono'] ?? $persona->Telefono,
                'TelefonoEmergencia' => $data['TelefonoEmergencia'] ?? $persona->TelefonoEmergencia,
                'Direccion' => $data['Direccion'] ?? $persona->Direccion,
                'Comuna' => $data['Comuna'] ?? $persona->Comuna,
            ]);
        }

        return response()->json($this->serialize($persona->fresh()->user));
    }
}
