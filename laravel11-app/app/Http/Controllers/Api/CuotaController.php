<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\CuotaTipo;
use App\Models\Documentos;
use App\Models\Persona;
use App\Models\User;
use App\Services\CuotaMensualService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CuotaController extends Controller
{
    private ?CuotaTipo $tipoMensualCache = null;
    private bool $tipoMensualCargado = false;

    public function __construct(private CuotaMensualService $cuotaMensualService)
    {
    }

    private function tipoMensual(): ?CuotaTipo
    {
        if (! $this->tipoMensualCargado) {
            $this->tipoMensualCache = $this->cuotaMensualService->tipoMensual();
            $this->tipoMensualCargado = true;
        }

        return $this->tipoMensualCache;
    }

    private function esTesoreria(Request $request): bool
    {
        $user = $request->user();

        return $user->isRole('Administrador') || $user->isCargo('Tesorero');
    }

    private function esMensual(?CuotaTipo $tipoMensual, ?int $idCuotaTipo, ?string $tipoCuotaTexto): bool
    {
        if (! $tipoMensual) {
            return false;
        }

        return $idCuotaTipo === $tipoMensual->id || $tipoCuotaTexto === $tipoMensual->nombre;
    }

    private function serializeCuota(Cuota $cuota, bool $conPersona = false): array
    {
        $estados = [1 => 'Pendiente', 2 => 'Aprobado', 3 => 'Rechazado', 4 => 'Cancelado', 5 => 'Pendiente Aprobacion'];

        $data = [
            'id' => $cuota->id,
            'virtual' => false,
            'esMensual' => $this->esMensual($this->tipoMensual(), $cuota->idCuotaTipo, $cuota->TipoCuota),
            'tipo' => $cuota->tipo?->nombre ?? $cuota->TipoCuota,
            'idCuotaTipo' => $cuota->idCuotaTipo,
            'periodo' => optional($cuota->FechaPeriodo)->format('Y-m-d'),
            'vencimiento' => optional($cuota->FechaVencimiento)->format('Y-m-d'),
            'fechaPago' => optional($cuota->FechaPago)->format('Y-m-d'),
            'monto' => (int) $cuota->Monto,
            'pendiente' => (int) $cuota->Pendiente,
            'recaudado' => (int) $cuota->Recaudado,
            'saldoFavor' => in_array($cuota->estadocuota?->Estado, ['Aprobado', 'Pagada']) ? (int) ($cuota->SaldoFavor ?? 0) : 0,
            'estado' => $cuota->Estado,
            'estadoLabel' => $cuota->estadocuota?->Estado ?? $estados[$cuota->Estado] ?? 'Desconocido',
            'motivoRechazo' => $cuota->MotivoRechazo,
            'aprobadoPor' => $cuota->aprobador?->name,
            'documento' => $cuota->documento ? [
                'id' => $cuota->documento->id,
                'nroDocumento' => $cuota->documento->NroDocumento,
                'url' => $cuota->documento->Path ? asset('storage/'.$cuota->documento->Path) : null,
            ] : null,
        ];

        if ($conPersona) {
            $data['persona'] = [
                'idUsuario' => $cuota->user?->id,
                'nombre' => $cuota->user?->name,
                'rut' => $cuota->user?->persona?->Rut,
            ];
        }

        return $data;
    }

    private function serializeVirtual(array $periodo, ?CuotaTipo $tipo): array
    {
        return [
            'id' => null,
            'virtual' => true,
            'esMensual' => true,
            'periodoKey' => $periodo['periodo']->format('Y-m'),
            'tipo' => $tipo?->nombre,
            'idCuotaTipo' => $tipo?->id,
            'periodo' => $periodo['periodo']->format('Y-m-d'),
            'vencimiento' => $periodo['vencimiento']->format('Y-m-d'),
            'fechaPago' => null,
            'monto' => $periodo['monto'],
            'pendiente' => $periodo['monto'],
            'recaudado' => 0,
            'saldoFavor' => 0,
            'estado' => 1,
            'estadoLabel' => 'Pendiente',
            'motivoRechazo' => null,
            'aprobadoPor' => null,
            'documento' => null,
        ];
    }

    public function mias(Request $request)
    {
        $user = $request->user();
        $persona = $user->persona;

        $reales = Cuota::where('idUser', $user->id)
            ->orderByDesc('FechaPeriodo')
            ->get()
            ->map(fn (Cuota $c) => $this->serializeCuota($c));

        $virtuales = $persona
            ? $this->cuotaMensualService->periodosPendientes($persona)
                ->map(fn ($p) => $this->serializeVirtual($p, $this->cuotaMensualService->tipoMensual()))
            : collect();

        $todas = $virtuales->concat($reales)->sortByDesc('periodo')->values();

        return response()->json([
            'cuotas' => $todas,
            'resumen' => [
                'pendientes' => $todas->whereIn('estado', [1, 5])->count(),
                'totalPendiente' => (int) $todas->whereIn('estado', [1, 5])->sum('pendiente'),
            ],
        ]);
    }

    public function index(Request $request)
    {
        abort_unless($this->esTesoreria($request), 403);

        $query = Cuota::query()->with(['user.persona', 'tipo', 'estadocuota', 'documento']);

        if ($estado = $request->query('estado')) {
            $query->where('Estado', $estado);
        }

        if ($idUser = $request->query('idUser')) {
            $query->where('idUser', $idUser);
        }

        if ($busqueda = $request->query('busqueda')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$busqueda}%"));
        }

        $cuotas = $query->orderByDesc('FechaPeriodo')->paginate(20);

        return response()->json([
            'data' => collect($cuotas->items())->map(fn (Cuota $c) => $this->serializeCuota($c, true)),
            'meta' => [
                'current_page' => $cuotas->currentPage(),
                'last_page' => $cuotas->lastPage(),
                'total' => $cuotas->total(),
            ],
        ]);
    }

    public function asignar(Request $request)
    {
        abort_unless($this->esTesoreria($request), 403);

        $data = $request->validate([
            'idUser' => ['required', 'exists:users,id'],
            'idCuotaTipo' => ['required', 'exists:cuotas_tipo,id'],
            'monto' => ['required', 'numeric', 'min:1'],
            'periodo' => ['required', 'date'],
            'vencimiento' => ['nullable', 'date'],
        ]);

        $tipo = CuotaTipo::findOrFail($data['idCuotaTipo']);
        abort_if($tipo->es_mensual, 422, 'Este tipo corresponde a la cuota mensual, que se genera automáticamente. Seleccione otro tipo de cargo.');

        $periodo = Carbon::parse($data['periodo']);
        $vencimiento = isset($data['vencimiento']) ? Carbon::parse($data['vencimiento']) : $periodo->copy()->lastOfMonth();

        $cuota = Cuota::create([
            'idUser' => $data['idUser'],
            'idCuotaTipo' => $tipo->id,
            'TipoCuota' => $tipo->nombre,
            'FechaPeriodo' => $periodo->format('Y-m-d'),
            'FechaVencimiento' => $vencimiento->format('Y-m-d'),
            'Estado' => 1,
            'Monto' => $data['monto'],
            'Pendiente' => $data['monto'],
            'Recaudado' => 0,
        ]);

        return response()->json($this->serializeCuota($cuota), 201);
    }

    public function pagar(Request $request, Cuota $cuota)
    {
        return $this->procesarPago($request, $cuota);
    }

    public function pagarPeriodoMensual(Request $request)
    {
        $periodoKey = $request->input('periodoKey');
        abort_unless($periodoKey, 422, 'Falta el periodo a pagar.');

        $persona = $request->user()->persona;
        abort_if(! $persona, 422, 'El usuario no tiene un perfil de socio asociado.');

        $cuota = $this->cuotaMensualService->obtenerOcrearCuota($persona, Carbon::createFromFormat('Y-m', $periodoKey));

        return $this->procesarPago($request, $cuota);
    }

    private function procesarPago(Request $request, Cuota $cuota)
    {
        $user = $request->user();
        abort_unless($cuota->idUser === $user->id || $this->esTesoreria($request), 403);
        abort_unless($cuota->Estado == 1 && $cuota->Pendiente > 0, 422, 'Esta cuota no admite pago en su estado actual.');

        $data = $request->validate([
            'montoPagar' => ['required', 'numeric', 'min:1'],
            'nroDocumento' => ['nullable', 'string', 'max:255'],
            'fechaPago' => ['required', 'date'],
            'documento' => ['required', 'file', 'max:10240'],
            'marcarAprobada' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('documento')->store('comprobantesCuotas', 'public');

        $this->registrarPago($cuota, $data, $path, $this->esTesoreria($request));

        return response()->json($this->serializeCuota($cuota->fresh(['estadocuota', 'documento', 'tipo', 'aprobador'])));
    }

    public function pagarMultiples(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cuotas,id'],
            'montoPagar' => ['required', 'numeric', 'min:1'],
            'nroDocumento' => ['nullable', 'string', 'max:255'],
            'fechaPago' => ['required', 'date'],
            'documento' => ['required', 'file', 'max:10240'],
            'marcarAprobada' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $cuotas = Cuota::whereIn('id', $data['ids'])->where('Estado', 1)->where('Pendiente', '>', 0)->get();
        abort_if($cuotas->isEmpty(), 422, 'No hay cuotas pagables en la selección.');

        $primerIdUser = $cuotas->first()->idUser;
        abort_unless($primerIdUser === $user->id || $this->esTesoreria($request), 403);

        $path = $request->file('documento')->store('comprobantesCuotas', 'public');
        $documento = Documentos::create([
            'TipoDocumento' => 11,
            'Nombre' => '',
            'NroDocumento' => $data['nroDocumento'] ?? null,
            'Path' => $path,
            'Descripcion' => 'Comprobante de pago de cuota',
        ]);
        $documento->update(['Nombre' => str_pad((string) $documento->id, 6, '0', STR_PAD_LEFT)]);

        $saldoDisponible = (float) $data['montoPagar'];
        $esTesoreria = $this->esTesoreria($request);
        $ultimaPagada = null;

        DB::transaction(function () use ($cuotas, &$saldoDisponible, $data, $documento, $esTesoreria, &$ultimaPagada) {
            foreach ($cuotas as $cuota) {
                if ($saldoDisponible < $cuota->Pendiente) {
                    break;
                }

                $saldoDisponible -= $cuota->Pendiente;

                $cuota->Recaudado = $cuota->Monto;
                $cuota->Pendiente = 0;
                $cuota->FechaPago = $data['fechaPago'];
                $cuota->idDocumento = $documento->id;
                $cuota->Estado = 5;

                if ($esTesoreria && ($data['marcarAprobada'] ?? false)) {
                    $cuota->Estado = 2;
                    $cuota->AprobadoPor = Auth::id();
                }

                $cuota->save();
                $ultimaPagada = $cuota;
            }
        });

        if ($ultimaPagada && $saldoDisponible > 0) {
            $ultimaPagada->update(['SaldoFavor' => $saldoDisponible]);
        }

        if (Cuota::where('idDocumento', $documento->id)->count() === 0) {
            $documento->delete();
        }

        return response()->json([
            'pagadas' => Cuota::where('idDocumento', $documento->id)->get()->map(fn ($c) => $this->serializeCuota($c)),
            'saldoFavorGenerado' => max($saldoDisponible, 0),
        ]);
    }

    private function registrarPago(Cuota $cuota, array $data, string $path, bool $puedeAprobar): void
    {
        $saldoFavor = Cuota::where('idUser', $cuota->idUser)
            ->where('SaldoFavor', '>', 0)
            ->whereHas('estadocuota', fn ($q) => $q->whereIn('Estado', ['Aprobado', 'Pagada']))
            ->first();

        $montoPagar = (float) $data['montoPagar'];

        if ($saldoFavor && $montoPagar >= $saldoFavor->SaldoFavor) {
            $montoPagar -= $saldoFavor->SaldoFavor;
            $saldoFavor->update(['SaldoFavor' => 0]);
        }

        $documento = Documentos::create([
            'TipoDocumento' => 11,
            'Nombre' => '',
            'NroDocumento' => $data['nroDocumento'] ?? null,
            'Path' => $path,
            'Descripcion' => 'Comprobante de pago de cuota',
        ]);
        $documento->update(['Nombre' => str_pad((string) $documento->id, 6, '0', STR_PAD_LEFT)]);

        $saldoRestante = max($montoPagar - $cuota->Pendiente, 0);

        $cuota->fill([
            'FechaPago' => $data['fechaPago'],
            'Pendiente' => 0,
            'Recaudado' => $cuota->Monto,
            'idDocumento' => $documento->id,
            'Estado' => 5,
        ]);

        if ($puedeAprobar && ($data['marcarAprobada'] ?? false)) {
            $cuota->Estado = 2;
            $cuota->AprobadoPor = Auth::id();
        }

        if ($saldoRestante > 0) {
            $cuota->SaldoFavor = $saldoRestante;
        }

        $cuota->save();
    }

    public function aprobar(Request $request, Cuota $cuota)
    {
        abort_unless($this->esTesoreria($request), 403);
        abort_unless($cuota->Estado == 5, 422, 'Solo se pueden aprobar cuotas pendientes de aprobación.');

        $cuota->update(['Estado' => 2, 'AprobadoPor' => Auth::id()]);

        return response()->json($this->serializeCuota($cuota->fresh(['estadocuota', 'aprobador'])));
    }

    public function aprobarLote(Request $request)
    {
        abort_unless($this->esTesoreria($request), 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:cuotas,id'],
        ]);

        Cuota::whereIn('id', $data['ids'])->where('Estado', 5)
            ->update(['Estado' => 2, 'AprobadoPor' => Auth::id()]);

        return response()->json(['ok' => true]);
    }

    public function rechazar(Request $request, Cuota $cuota)
    {
        abort_unless($this->esTesoreria($request), 403);
        abort_unless($cuota->Estado == 5, 422, 'Solo se pueden rechazar cuotas pendientes de aprobación.');

        $data = $request->validate([
            'motivoRechazo' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($cuota, $data) {
            $cuota->update(['Estado' => 3, 'MotivoRechazo' => $data['motivoRechazo']]);

            Cuota::create([
                'idUser' => $cuota->idUser,
                'idCuotaTipo' => $cuota->idCuotaTipo,
                'FechaPeriodo' => $cuota->FechaPeriodo,
                'FechaVencimiento' => $cuota->FechaVencimiento,
                'Monto' => $cuota->Monto,
                'Pendiente' => $cuota->Monto,
                'Recaudado' => 0,
                'SaldoFavor' => 0,
                'TipoCuota' => $cuota->TipoCuota,
                'Estado' => 1,
            ]);
        });

        return response()->json($this->serializeCuota($cuota->fresh(['estadocuota'])));
    }

    /** Disparo manual del recordatorio de cuotas vencidas (además del envío automático semanal). */
    public function enviarRecordatorios(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $atrasados = $this->cuotaMensualService->personasConAtraso();

        foreach ($atrasados as $atraso) {
            $this->cuotaMensualService->enviarRecordatorio($atraso);
        }

        return response()->json(['enviados' => $atrasados->count()]);
    }
}
