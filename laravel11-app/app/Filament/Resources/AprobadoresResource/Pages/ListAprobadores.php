<?php

namespace App\Filament\Resources\AprobadoresResource\Pages;

use App\Filament\Resources\AprobadoresResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAprobadores extends ListRecords
{
    protected static string $resource = AprobadoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }
}
