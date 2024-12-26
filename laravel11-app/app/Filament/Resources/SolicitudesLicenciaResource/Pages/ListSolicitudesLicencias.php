<?php

namespace App\Filament\Resources\SolicitudesLicenciaResource\Pages;

use App\Filament\Resources\SolicitudesLicenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudesLicencias extends ListRecords
{
    protected static string $resource = SolicitudesLicenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
