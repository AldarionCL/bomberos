<?php

namespace App\Filament\Resources\SolicitudesBajaResource\Pages;

use App\Filament\Resources\SolicitudesBajaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudesBaja extends EditRecord
{
    protected static string $resource = SolicitudesBajaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
