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
        $egresos = Caja::where('tipo', 'Egreso')->sum('total');

        $formatter = new NumberFormatter('es_CL', NumberFormatter::CURRENCY);

        return [
            Stat::make('Total en Caja', $formatter->formatCurrency($total, 'CLP')),
            Stat::make('Total Ingresos (Cuotas)', $formatter->formatCurrency($ingresos, 'CLP')),
            Stat::make('Total Egresos', $formatter->formatCurrency($egresos, 'CLP')),
        ];
    }
}
