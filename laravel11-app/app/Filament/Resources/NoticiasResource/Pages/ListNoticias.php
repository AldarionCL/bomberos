<?php

namespace App\Filament\Resources\NoticiasResource\Pages;

use App\Filament\Resources\NoticiasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNoticias extends ListRecords
{
    protected static string $resource = NoticiasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
