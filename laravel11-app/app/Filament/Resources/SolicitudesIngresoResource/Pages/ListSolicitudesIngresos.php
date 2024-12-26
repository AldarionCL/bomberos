<?php

namespace App\Filament\Resources\SolicitudesIngresoResource\Pages;

use App\Filament\Resources\SolicitudesIngresoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudesIngresos extends ListRecords
{
    protected static string $resource = SolicitudesIngresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
