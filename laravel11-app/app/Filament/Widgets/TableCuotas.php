<?php

namespace App\Filament\Widgets;

use App\Models\Cuota;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TableCuotas extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Cuotas Futuras';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cuota::query()->where('idUser', auth()->id())
                    ->where('Estado', 1)
            )
//            ->description('Listado de cuotas pendientes')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('FechaPeriodo')->label('Periodo')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('Monto')->label('Monto')->money('CLP'),
                Tables\Columns\TextColumn::make('FechaVencimiento')->label('Fecha Vencimiento')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('estadocuota.Estado')->label('Estado')
                    ->badge()
                    ->color('warning'),
            ]);
    }
}
