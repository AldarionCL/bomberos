<?php

namespace App\Filament\Resources\PrecioCuotasResource\Pages;

use App\Filament\Resources\PrecioCuotasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrecioCuotas extends EditRecord
{
    protected static string $resource = PrecioCuotasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
