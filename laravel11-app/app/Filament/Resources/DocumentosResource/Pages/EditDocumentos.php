<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentos extends EditRecord
{
    protected static string $resource = DocumentosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
