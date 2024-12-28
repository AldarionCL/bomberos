<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
