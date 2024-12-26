<?php

namespace App\Filament\Resources\TesoreriaResource\Pages;

use App\Filament\Resources\TesoreriaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTesorerias extends ListRecords
{
    protected static string $resource = TesoreriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
