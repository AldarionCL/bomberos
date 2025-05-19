<?php

namespace App\Filament\Widgets;

use App\Models\Cuota;
use App\Models\Solicitud;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsVoluntario extends BaseWidget
{
    protected function getStats(): array
    {
        $estado = \Auth::user()->persona->estado->Estado;
        if ($estado == 'Activo') {
            $estado = 'Activo';
            $color = 'success';
            $icono = 'heroicon-s-check-circle';
            $textoDescripcion = 'El voluntario se encuentra activo';
        } else if ($estado == 'Inactivo') {
            $estado = 'Inactivo';
            $color = 'danger';
            $icono = 'heroicon-s-x-circle';
            $textoDescripcion = 'El voluntario se encuentra inactivo';
        } else {
            $estado = 'Desconocido';
            $color = 'warning';
            $icono = 'heroicon-s-exclamation-circle';
            $textoDescripcion = 'El estado del voluntario es desconocido';
        }


        $licencias = Solicitud::where('AsociadoA', \Auth::user()->id)
            ->whereIn('TipoSolicitud', [3, 4])
            ->where('Estado', 1)
            ->where('FechaDesde', '<=', Carbon::now())
            ->where('FechaHasta', '>=', Carbon::now())
            ->first();
        if ($licencias) {
            $diasLicencia = Carbon::now()->diffInDays($licencias->FechaHasta);
        } else {
            $diasLicencia = 0;
        }

        $cuotas = Cuota::query()
            ->where('idUser', auth()->id())
            ->where('FechaPeriodo', '<=', Carbon::now())
            ->where('FechaVencimiento', '>=', Carbon::now())
            ->first();
        $estadoCuota = $cuotas->estadocuota->Estado;


        return [
//            Stat::make('Voluntario', \Auth::user()->name),
            Stat::make('Estado', $estado)
                ->description($textoDescripcion)
                ->icon($icono)
                ->color($color),

            Stat::make('Licencia', $diasLicencia)
                ->description('Días de licencia')
                ->icon('heroicon-s-calendar')
                ->color('warning'),

            Stat::make('Estado de cuota actual', $estadoCuota)
                ->description('Cuota del periodo : ' . (($cuotas) ? Carbon::parse($cuotas->FechaPeriodo)->format('d/m/Y') : 'No disponible'))
                ->icon('heroicon-s-credit-card')
                ->color('danger'),
        ];
    }
}
