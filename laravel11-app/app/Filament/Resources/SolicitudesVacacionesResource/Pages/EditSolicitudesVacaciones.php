<?php

namespace App\Filament\Resources\SolicitudesVacacionesResource\Pages;

use App\Filament\Resources\SolicitudesVacacionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudesVacaciones extends EditRecord
{
    protected static string $resource = SolicitudesVacacionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
