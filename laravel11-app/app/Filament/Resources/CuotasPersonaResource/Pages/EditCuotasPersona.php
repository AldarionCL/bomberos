<?php

namespace App\Filament\Resources\CuotasPersonaResource\Pages;

use App\Filament\Resources\CuotasPersonaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCuotasPersona extends EditRecord
{
    protected static string $resource = CuotasPersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\DeleteAction::make(),
        ];
    }
}
