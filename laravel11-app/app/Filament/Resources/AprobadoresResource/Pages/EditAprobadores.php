<?php

namespace App\Filament\Resources\AprobadoresResource\Pages;

use App\Filament\Resources\AprobadoresResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAprobadores extends EditRecord
{
    protected static string $resource = AprobadoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
