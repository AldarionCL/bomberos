<?php

namespace App\Filament\Widgets;

use App\Models\Gastos;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter;

class GastosStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalGastos = abs(Gastos::sum('MontoTotal'));

        $formatter = new NumberFormatter('es_CL', NumberFormatter::CURRENCY);

        return [
            Stat::make('Total Gastos', $formatter->formatCurrency($totalGastos, 'CLP'))
                ->description('Suma de todos los egresos registrados')
                ->color('danger')
                ->icon('heroicon-o-arrow-trending-down'),
        ];
    }
}
