<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\CuotaTipo;
use App\Models\Persona;
use App\Models\PrecioCuotas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PersonaController extends Controller
{
    private function esTesoreria(Request $request): bool
    {
        $user = $request->user();

        return $user->isRole('Administrador') || $user->isCargo('Tesorero');
    }

    /** Puede crear/editar la ficha completa de cualquier socio (no solo la propia). */
    private function puedeGestionar(Request $request): bool
    {
        return $request->user()->can('update', Persona::class);
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
                'FechaNacimiento' => optional($persona->FechaNacimiento)->format('Y-m-d'),
                'FechaReclutamiento' => optional($persona->FechaReclutamiento)->format('Y-m-d'),
                'Nacionalidad' => $persona->Nacionalidad,
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
                'Foto' => $persona->Foto ? asset('storage/'.$persona->Foto) : null,
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
            'omitirCuotaInscripcion' => ['boolean'],
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

        if (! ($data['omitirCuotaInscripcion'] ?? false)) {
            $this->generarCuotaInscripcion($user, Carbon::parse($data['FechaReclutamiento']));
        }

        return response()->json($this->serialize($user->fresh()), 201);
    }

    /**
     * Genera, al crear un socio, el cobro único de la cuota de inscripción
     * (si hay un tipo designado para ello y tiene un valor configurado).
     */
    private function generarCuotaInscripcion(User $user, Carbon $fechaReclutamiento): void
    {
        $tipo = CuotaTipo::where('es_cuota_inscripcion', true)->first();
        if (! $tipo) {
            return;
        }

        $monto = PrecioCuotas::vigentePara($tipo->nombre);
        if ($monto <= 0) {
            return;
        }

        Cuota::create([
            'idUser' => $user->id,
            'idCuotaTipo' => $tipo->id,
            'TipoCuota' => $tipo->nombre,
            'FechaPeriodo' => $fechaReclutamiento->format('Y-m-d'),
            'FechaVencimiento' => $fechaReclutamiento->copy()->addDays(30)->format('Y-m-d'),
            'Estado' => 1,
            'Monto' => $monto,
            'Pendiente' => $monto,
            'Recaudado' => 0,
        ]);
    }

    public function update(Request $request, Persona $persona)
    {
        $user = $request->user();
        $puedeGestionar = $this->puedeGestionar($request);
        $esPropio = $user->id === $persona->idUsuario;
        abort_unless($puedeGestionar || $esPropio, 403);

        // Campos personales/descriptivos: cualquiera puede editarlos en su propia
        // ficha (igual que el mantenedor de Filament). Los organizacionales
        // (cargo, estado, activo, rol) quedan reservados a quienes administran
        // socios, para evitar que alguien se autoasigne un cargo o se reactive.
        $rules = [
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$persona->idUsuario],
            'password' => ['nullable', 'string', 'min:6'],
            'Rut' => ['sometimes', 'string', 'unique:personas,Rut,'.$persona->id],
            'Telefono' => ['nullable', 'string', 'max:30'],
            'TelefonoEmergencia' => ['nullable', 'string', 'max:30'],
            'FechaNacimiento' => ['sometimes', 'date'],
            'Nacionalidad' => ['nullable', 'string', 'max:255'],
            'Direccion' => ['nullable', 'string', 'max:255'],
            'Comuna' => ['nullable', 'string', 'max:255'],
            'NivelEstudio' => ['nullable', 'string', 'max:255'],
            'Ocupacion' => ['nullable', 'string', 'max:255'],
            'LugarOcupacion' => ['nullable', 'string', 'max:255'],
            'EstadoCivil' => ['nullable', 'string', 'max:255'],
            'GrupoSanguineo' => ['nullable', 'string', 'max:255'],
            'TallaZapatos' => ['nullable', 'string', 'max:50'],
            'TallaPantalon' => ['nullable', 'string', 'max:50'],
            'TallaCamisa' => ['nullable', 'string', 'max:50'],
            'TallaChaqueta' => ['nullable', 'string', 'max:50'],
            'TallaSombrero' => ['nullable', 'string', 'max:50'],
            'Observaciones' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ];

        if ($puedeGestionar) {
            $rules['idRole'] = ['sometimes', 'exists:user_roles,id'];
            $rules['idCargo'] = ['sometimes', 'exists:persona_cargos,id'];
            $rules['idEstado'] = ['sometimes', 'exists:persona_estados,id'];
            $rules['FechaReclutamiento'] = ['sometimes', 'date'];
            $rules['Activo'] = ['sometimes', 'boolean'];
        }

        $data = $request->validate($rules);

        $personaData = collect($data)->except(['name', 'email', 'password', 'idRole', 'foto'])->toArray();

        if ($request->hasFile('foto')) {
            $personaData['Foto'] = $request->file('foto')->store('fotosPersonas', 'public');
        }

        $persona->update($personaData);

        $userData = collect($data)->only(['name', 'email', 'idRole'])->toArray();
        if (! empty($data['password'])) {
            $userData['password'] = Hash::make($data['password']);
        }
        if (! empty($userData)) {
            $persona->user->update($userData);
        }

        return response()->json($this->serialize($persona->fresh()->user));
    }
}
