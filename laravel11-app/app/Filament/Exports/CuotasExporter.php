<?php

namespace App\Filament\Exports;

use App\Models\Cuota;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class CuotasExporter extends Exporter
{
    protected static ?string $model = Cuota::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('FechaPeriodo')
                ->label('Fecha de Periodo'),
            ExportColumn::make('FechaVencimiento')
                ->label('Fecha de Vencimiento'),
            ExportColumn::make('user.persona.Rut')
                ->label('Rut'),
            ExportColumn::make('user.name')
                ->label('Persona'),
            ExportColumn::make('user.persona.cargo.Cargo')
                ->label('Cargo'),
            ExportColumn::make('Monto')
                ->label('Monto')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('Pendiente')
                ->label('Pendiente')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('Recaudado')
                ->label('Recaudado')
                ->formatStateUsing(fn ($state) => '$' . number_format($state, 0, ',', '.')),
            ExportColumn::make('estadocuota.Estado')
                ->label('Estado'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your cuotas export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
