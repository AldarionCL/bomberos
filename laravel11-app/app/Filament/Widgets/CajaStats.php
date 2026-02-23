<?php

namespace App\Filament\Widgets;

use App\Models\Caja;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter;

class CajaStats extends BaseWidget
{
    protected function getStats(): array
    {
        $total = Caja::sum('total');
        $ingresos = Caja::where('tipo', 'Ingreso cuota')->sum('total');
        $egresos = abs(Caja::where('tipo', 'Egreso')->sum('total'));

        $formatter = new NumberFormatter('es_CL', NumberFormatter::CURRENCY);

        return [
            Stat::make('Total en Caja', $formatter->formatCurrency($total, 'CLP'))
                ->description('Balance general')
                ->color('success'),
            Stat::make('Total Ingresos (Cuotas)', $formatter->formatCurrency($ingresos, 'CLP'))
                ->description('Ingresos por cuotas')
                ->color('info'),
            Stat::make('Total Egresos', $formatter->formatCurrency($egresos, 'CLP'))
                ->description('Gastos realizados')
                ->color('danger'),
        ];
    }
}
