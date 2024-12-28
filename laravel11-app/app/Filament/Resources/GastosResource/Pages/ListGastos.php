<?php

namespace App\Filament\Resources\GastosResource\Pages;

use App\Filament\Resources\GastosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGastos extends ListRecords
{
    protected static string $resource = GastosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
