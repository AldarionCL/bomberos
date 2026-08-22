<?php

namespace App\Services;

use App\Models\Cuota;
use App\Models\CuotaTipo;
use App\Models\Persona;
use App\Models\PrecioCuotas;
use Carbon\Carbon;
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

        $tipoVoluntario ??= 'miembro';

        $precio = PrecioCuotas::where('TipoCuota', $tipo->nombre)
            ->where('TipoVoluntario', $tipoVoluntario)
            ->orderByDesc('id')
            ->first()
            ?? PrecioCuotas::where('TipoCuota', $tipo->nombre)->orderByDesc('id')->first();

        return (int) ($precio?->Monto ?? 0);
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
