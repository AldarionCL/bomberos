<?php

namespace App\Filament\Resources\SolicitudesIngresoResource\Pages;

use App\Filament\Resources\SolicitudesIngresoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudesIngreso extends EditRecord
{
    protected static string $resource = SolicitudesIngresoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
