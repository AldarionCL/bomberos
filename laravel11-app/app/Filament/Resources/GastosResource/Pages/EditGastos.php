<?php

namespace App\Filament\Resources\GastosResource\Pages;

use App\Filament\Resources\GastosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGastos extends EditRecord
{
    protected static string $resource = GastosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
