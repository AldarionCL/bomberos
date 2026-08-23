<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aprobaciones;
use App\Models\Aprobadores;
use App\Models\Solicitud;
use App\Models\SolicitudesTipo;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

/**
 * Autoservicio de "Solicitud de Licencia" (tipos 3 y 4 de SolicitudesTipo).
 * La aprobación sigue ocurriendo en el panel Filament (AprobacionesRelationManager),
 * que ya sabe leer las filas que este controlador crea.
 */
class SolicitudLicenciaController extends Controller
{
    private const TOPE_DIAS = [3 => 30, 4 => 180];

    private function puedeSolicitarPorOtro(Request $request): bool
    {
        $user = $request->user();

        return $user->isRole('Administrador') || $user->isCargo(['Director', 'Capitan', 'Capitán']);
    }

    /** Tipos de solicitud de licencia que realmente tienen un aprobador activo configurado. */
    private function tiposConAprobador()
    {
        $idsConAprobador = Aprobadores::where('Activo', 1)
            ->whereIn('idSolicitudTipo', array_keys(self::TOPE_DIAS))
            ->pluck('idSolicitudTipo')
            ->unique();

        return SolicitudesTipo::whereIn('id', $idsConAprobador)->get(['id', 'Tipo', 'Descripcion']);
    }

    private function serialize(Solicitud $solicitud): array
    {
        return [
            'id' => $solicitud->id,
            'tipo' => $solicitud->tipo?->Tipo,
            'idTipo' => $solicitud->TipoSolicitud,
            'fechaRegistro' => optional($solicitud->Fecha_registro)->format('Y-m-d'),
            'fechaDesde' => optional($solicitud->FechaDesde)->format('Y-m-d'),
            'fechaHasta' => optional($solicitud->FechaHasta)->format('Y-m-d'),
            'diasHabiles' => $solicitud->DiasHabiles,
            'observaciones' => $solicitud->Observaciones,
            'estado' => $solicitud->Estado,
            'estadoLabel' => match ((int) $solicitud->Estado) {
                0 => 'Pendiente',
                1 => 'Aprobado',
                2 => 'Rechazado',
                default => 'Desconocido',
            },
            'asociado' => $solicitud->asociado?->name,
            'aprobaciones' => $solicitud->aprobaciones->map(fn ($a) => [
                'aprobador' => $a->aprobador?->name,
                'estado' => $a->Estado,
                'estadoLabel' => $a->Estado == 1 ? 'Aprobado' : ($a->Estado == 2 ? 'Rechazado' : 'Pendiente'),
                'fechaAprobacion' => optional($a->FechaAprobacion)->format('Y-m-d'),
            ]),
        ];
    }

    public function mias(Request $request)
    {
        $user = $request->user();

        $solicitudes = Solicitud::with(['tipo', 'asociado', 'aprobaciones.aprobador'])
            ->whereIn('TipoSolicitud', array_keys(self::TOPE_DIAS))
            ->where('AsociadoA', $user->id)
            ->orderByDesc('Fecha_registro')
            ->get();

        $disponibilidad = collect(self::TOPE_DIAS)->map(function ($tope, $idTipo) use ($user) {
            $usados = (int) Solicitud::where('AsociadoA', $user->id)
                ->where('Estado', 1)
                ->where('TipoSolicitud', $idTipo)
                ->where('FechaDesde', '>=', Carbon::now()->firstOfYear()->format('Y-m-d'))
                ->sum('DiasHabiles');

            return ['idTipo' => $idTipo, 'diasUsados' => $usados, 'diasTope' => $tope, 'diasDisponibles' => max($tope - $usados, 0)];
        })->values();

        return response()->json([
            'solicitudes' => $solicitudes->map(fn ($s) => $this->serialize($s)),
            'disponibilidad' => $disponibilidad,
            'tiposDisponibles' => $this->tiposConAprobador(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'idTipo' => ['required', 'in:3,4'],
            'fechaDesde' => ['required', 'date'],
            'fechaHasta' => ['required', 'date', 'after_or_equal:fechaDesde'],
            'observaciones' => ['nullable', 'string'],
            'asociadoA' => ['nullable', 'exists:users,id'],
        ]);

        $user = $request->user();
        $idUsuarioSolicitud = $user->id;

        if (! empty($data['asociadoA']) && (int) $data['asociadoA'] !== $user->id) {
            abort_unless($this->puedeSolicitarPorOtro($request), 403);
            $idUsuarioSolicitud = (int) $data['asociadoA'];
        }

        abort_unless(
            Aprobadores::where('idSolicitudTipo', $data['idTipo'])->where('Activo', 1)->exists(),
            422,
            'Este tipo de solicitud no tiene un aprobador configurado todavía. Contacta a un administrador.'
        );

        $solicitud = new Solicitud();
        $dias = $solicitud->calculaDiasHabiles($data['fechaDesde'], $data['fechaHasta']);

        abort_unless(
            Solicitud::verificaDiasDisponibles($idUsuarioSolicitud, $dias, (int) $data['idTipo']),
            422,
            'El rango solicitado supera los días disponibles para este tipo de licencia en el año.'
        );

        $solicitud->fill([
            'TipoSolicitud' => $data['idTipo'],
            'Estado' => 0,
            'Fecha_registro' => Carbon::today()->format('Y-m-d'),
            'SolicitadoPor' => $user->id,
            'AsociadoA' => $idUsuarioSolicitud,
            'Observaciones' => $data['observaciones'] ?? null,
            'FechaDesde' => $data['fechaDesde'],
            'FechaHasta' => $data['fechaHasta'],
            'DiasHabiles' => $dias,
        ]);
        $solicitud->save();

        $aprobadores = Aprobadores::where('idSolicitudTipo', $data['idTipo'])->where('Activo', 1)->get();
        $notificar = [];
        foreach ($aprobadores as $aprobador) {
            Aprobaciones::create([
                'idSolicitud' => $solicitud->id,
                'idAprobador' => $aprobador->idAprobador,
                'Orden' => $aprobador->Orden,
                'Estado' => 0,
                'FechaAprobacion' => null,
            ]);
            $notificar[] = $aprobador->idAprobador;
        }

        if ($notificar) {
            Notification::make()
                ->title('Nueva solicitud de licencia')
                ->body(($solicitud->asociado->name ?? 'Un socio').' solicitó licencia del '.$solicitud->FechaDesde->format('d/m/Y').' al '.$solicitud->FechaHasta->format('d/m/Y').'.')
                ->info()
                ->sendToDatabase(User::whereIn('id', $notificar)->get());
        }

        return response()->json($this->serialize($solicitud->fresh(['tipo', 'asociado', 'aprobaciones.aprobador'])), 201);
    }
}
