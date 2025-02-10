<?php

namespace App\Filament\Resources\SolicitudesTipoResource\Pages;

use App\Filament\Resources\SolicitudesTipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSolicitudesTipos extends ManageRecords
{
    protected static string $resource = SolicitudesTipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
