<?php

namespace App\Filament\Resources\PrecioCuotasResource\Pages;

use App\Filament\Resources\PrecioCuotasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPrecioCuotas extends ListRecords
{
    protected static string $resource = PrecioCuotasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
