<?php

namespace App\Filament\Widgets;

use App\Models\Cuota;
use Carbon\Carbon;
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
                    ->where('FechaVencimiento', '<=', now()->lastOfMonth()->format('Y-m-d'))
            )
//            ->description('Listado de cuotas pendientes')
            ->defaultPaginationPageOption(5)
            ->columns([
                Tables\Columns\TextColumn::make('TipoCuota')->label('Tipo Cuota')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cuota_ordinaria' => 'Cuota Ordinaria',
                        'cuota_extraordinaria' => 'Cuota Extraordinaria',
                        default => ucwords(str_replace('_', ' ', strtolower($state))),
                    }),
                Tables\Columns\TextColumn::make('FechaPeriodo')->label('Periodo')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('Monto')->label('Monto')->money('CLP'),
                Tables\Columns\TextColumn::make('FechaVencimiento')->label('Fecha Vencimiento')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('vencimiento')
                    ->default(fn($record) => (Carbon::parse($record->FechaVencimiento)->isPast()) ? 'Vencida' : Carbon::parse($record->FechaVencimiento)->diffForHumans())
                    ->badge()
                    ->color(fn($record) => (Carbon::parse($record->FechaVencimiento)->isPast()) ? 'danger' : 'warning'),

                /*Tables\Columns\TextColumn::make('estadocuota.Estado')->label('Estado')
                    ->badge()
                    ->color('warning'),*/
            ]);
    }
}
