<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\CuotaTipo;
use App\Models\Persona;
use App\Models\PrecioCuotas;
use App\Notifications\CuotaVencidaNotification;
use Carbon\Carbon;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Collection;

/**
 * Calcula y otorga al vuelo las cuotas mensuales de un socio.
 *
 * Reemplaza el antiguo flujo de "generación masiva de cuotas" (botón manual /
 * comando que había que ejecutar mes a mes). En vez de pre-crear filas en la
 * tabla `cuotas` para cada socio y cada periodo futuro, este servicio calcula
 * los periodos mensuales pendientes on-demand y solo persiste una fila real
 * cuando el socio efectivamente paga (o cuando el tesorero le asigna la cuota
 * manualmente). Así nunca queda "atrasado" un proceso batch.
 */
class CuotaMensualService
{
    /** No generamos periodos virtuales más atrás que esta cantidad de meses. */
    private const MESES_ATRAS_MAXIMO = 24;

    public function tipoMensual(): ?CuotaTipo
    {
        return CuotaTipo::where('es_mensual', true)->first();
    }

    public function montoVigente(?string $tipoVoluntario = null): int
    {
        $tipo = $this->tipoMensual();
        if (! $tipo) {
            return 0;
        }

        return PrecioCuotas::vigentePara($tipo->nombre, $tipoVoluntario ?? 'miembro');
    }

    /**
     * Periodos mensuales (primer día de cada mes) entre el inicio de cobro de
     * la persona y el mes actual que todavía no tienen una fila de Cuota.
     *
     * @return Collection<int, array{periodo: Carbon, vencimiento: Carbon, monto: int}>
     */
    public function periodosPendientes(Persona $persona): Collection
    {
        $tipoMensual = $this->tipoMensual();
        if (! $tipoMensual || ! $persona->Activo) {
            return collect();
        }

        $inicio = $persona->FechaReclutamiento
            ? Carbon::parse($persona->FechaReclutamiento)->startOfMonth()
            : now()->startOfMonth();

        $limiteAtras = now()->subMonths(self::MESES_ATRAS_MAXIMO)->startOfMonth();
        if ($inicio->lt($limiteAtras)) {
            $inicio = $limiteAtras;
        }

        $fin = now()->startOfMonth();
        if ($inicio->gt($fin)) {
            return collect();
        }

        $existentes = Cuota::where('idUser', $persona->idUsuario)
            ->where(function ($q) use ($tipoMensual) {
                $q->where('idCuotaTipo', $tipoMensual->id)
                    ->orWhere('TipoCuota', $tipoMensual->nombre);
            })
            ->whereBetween('FechaPeriodo', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->pluck('FechaPeriodo')
            ->map(fn ($fecha) => Carbon::parse($fecha)->format('Y-m'))
            ->all();

        $monto = $this->montoVigente($persona->TipoVoluntario);
        $pendientes = collect();

        for ($cursor = $inicio->copy(); $cursor->lte($fin); $cursor->addMonth()) {
            if (in_array($cursor->format('Y-m'), $existentes, true)) {
                continue;
            }

            $pendientes->push([
                'periodo' => $cursor->copy(),
                'vencimiento' => $cursor->copy()->lastOfMonth(),
                'monto' => $monto,
            ]);
        }

        return $pendientes;
    }

    /**
     * Un socio está "al día" si no tiene periodos mensuales pendientes vencidos
     * ni cuotas reales (de cualquier tipo) pendientes/atrasadas.
     */
    public function estaAlDia(Persona $persona): bool
    {
        $hoy = now()->startOfDay();

        $virtualVencido = $this->periodosPendientes($persona)
            ->contains(fn ($p) => $p['vencimiento']->lt($hoy));

        if ($virtualVencido) {
            return false;
        }

        return ! Cuota::where('idUser', $persona->idUsuario)
            ->whereIn('Estado', [1, 5])
            ->where('Pendiente', '>', 0)
            ->where('FechaVencimiento', '<', $hoy->format('Y-m-d'))
            ->exists();
    }

    /**
     * Socios activos que tienen al menos una cuota (virtual o real) vencida y
     * sin pagar. Base para los recordatorios automáticos.
     *
     * @return Collection<int, array{persona: Persona, virtuales: Collection, reales: Collection}>
     */
    public function personasConAtraso(): Collection
    {
        $hoy = now()->startOfDay();

        return Persona::where('Activo', 1)->get()
            ->map(function (Persona $persona) use ($hoy) {
                $virtuales = $this->periodosPendientes($persona)
                    ->filter(fn ($p) => $p['vencimiento']->lt($hoy))
                    ->values();

                $reales = Cuota::where('idUser', $persona->idUsuario)
                    ->whereIn('Estado', [1, 5])
                    ->where('Pendiente', '>', 0)
                    ->where('FechaVencimiento', '<', $hoy->format('Y-m-d'))
                    ->get();

                if ($virtuales->isEmpty() && $reales->isEmpty()) {
                    return null;
                }

                return ['persona' => $persona, 'virtuales' => $virtuales, 'reales' => $reales];
            })
            ->filter()
            ->values();
    }

    /**
     * Envía el recordatorio (email + notificación in-app) a un socio con
     * cuotas atrasadas. $atraso es un elemento de personasConAtraso().
     */
    public function enviarRecordatorio(array $atraso): void
    {
        $persona = $atraso['persona'];
        $user = $persona->user;
        if (! $user) {
            return;
        }

        $items = collect();
        foreach ($atraso['virtuales'] as $v) {
            $items->push(['label' => 'Cuota mensual de '.$v['periodo']->translatedFormat('F Y'), 'monto' => $v['monto']]);
        }
        foreach ($atraso['reales'] as $cuota) {
            $items->push(['label' => ($cuota->tipo?->nombre ?? $cuota->TipoCuota).' — vencida el '.Carbon::parse($cuota->FechaVencimiento)->format('d/m/Y'), 'monto' => (int) $cuota->Pendiente]);
        }

        $total = $items->sum('monto');

        $user->notify(new CuotaVencidaNotification($items, $total));

        FilamentNotification::make()
            ->title('Tienes cuotas vencidas')
            ->body('Monto total pendiente: $'.number_format($total, 0, ',', '.').'. Ingresa a Mis Cuotas para regularizar.')
            ->warning()
            ->sendToDatabase($user);
    }

    /**
     * Devuelve la fila real de la cuota mensual de un periodo, creándola si
     * todavía no existe (creación perezosa, solo al momento de necesitarla).
     */
    public function obtenerOcrearCuota(Persona $persona, Carbon $periodo): Cuota
    {
        $tipoMensual = $this->tipoMensual();
        abort_if(! $tipoMensual, 422, 'No hay un tipo de cuota mensual configurado.');

        $periodoInicioMes = $periodo->copy()->startOfMonth();

        $cuota = Cuota::where('idUser', $persona->idUsuario)
            ->where(function ($q) use ($tipoMensual) {
                $q->where('idCuotaTipo', $tipoMensual->id)
                    ->orWhere('TipoCuota', $tipoMensual->nombre);
            })
            ->whereDate('FechaPeriodo', $periodoInicioMes->format('Y-m-d'))
            ->first();

        if ($cuota) {
            return $cuota;
        }

        $monto = $this->montoVigente($persona->TipoVoluntario);

        return Cuota::create([
            'idUser' => $persona->idUsuario,
            'idCuotaTipo' => $tipoMensual->id,
            'TipoCuota' => $tipoMensual->nombre,
            'FechaPeriodo' => $periodoInicioMes->format('Y-m-d'),
            'FechaVencimiento' => $periodoInicioMes->copy()->lastOfMonth()->format('Y-m-d'),
            'Estado' => 1,
            'Monto' => $monto,
            'Pendiente' => $monto,
            'Recaudado' => 0,
        ]);
    }
}
