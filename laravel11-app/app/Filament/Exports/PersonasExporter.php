<?php

namespace App\Filament\Exports;

use App\Models\Personas;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PersonasExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('persona.Rut')
                ->label('Rut'),
            ExportColumn::make('name')
                ->label('Nombre'),
            ExportColumn::make('email')
                ->label('Email'),
            ExportColumn::make('persona.cargo.Cargo')
                ->label('Cargo'),
            ExportColumn::make('persona.FechaNacimiento')
                ->label('Fecha de Nacimiento'),
            ExportColumn::make('persona.Sexo')
                ->label('Sexo'),
            ExportColumn::make('persona.Telefono')
                ->label('Teléfono'),
            ExportColumn::make('persona.EstadoCivil')
                ->label('Estado Civil'),
            ExportColumn::make('persona.Nacionalidad')
                ->label('Nacionalidad'),
            ExportColumn::make('persona.GrupoSanguineo')
                ->label('Grupo Sanguíneo'),
            ExportColumn::make('persona.Direccion')
                ->label('Dirección'),
            ExportColumn::make('persona.Comuna')
                ->label('Comuna'),
            ExportColumn::make('persona.Observaciones')
                ->label('Observaciones'),
            ExportColumn::make('persona.NivelEstudio')
                ->label('Nivel de Estudio'),
            ExportColumn::make('persona.FechaReclutamiento')
                ->label('Fecha de Reclutamiento'),
            ExportColumn::make('persona.Edad')
                ->label('Edad'),
            ExportColumn::make('persona.Activo')
                ->label('Activo'),

        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your personas export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
