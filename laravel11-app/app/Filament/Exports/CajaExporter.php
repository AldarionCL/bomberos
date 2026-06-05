<?php

namespace App\Filament\Exports;

use App\Models\Caja;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CajaExporter extends Exporter
{
    protected static ?string $model = Caja::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label('Fecha')
                ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i') : ''),
            ExportColumn::make('descripcion')
                ->label('Descripción'),
            ExportColumn::make('monto')
                ->label('Monto')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('impuesto')
                ->label('Impuesto')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('total')
                ->label('Total')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('tipo')
                ->label('Tipo'),
            ExportColumn::make('usuario.name')
                ->label('Usuario'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de Caja se completó con ' . number_format($export->successful_rows) . ' ' . str('fila')->plural($export->successful_rows) . ' exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron.';
        }

        return $body;
    }
}
