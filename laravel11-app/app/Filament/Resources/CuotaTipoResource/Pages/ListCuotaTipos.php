<?php

namespace App\Filament\Resources\CuotaTipoResource\Pages;

use App\Filament\Resources\CuotaTipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCuotaTipos extends ListRecords
{
    protected static string $resource = CuotaTipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalWidth('lg'),
        ];
    }
}
