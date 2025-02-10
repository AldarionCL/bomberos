<?php

namespace App\Filament\Resources\DocumentosTipoResource\Pages;

use App\Filament\Resources\DocumentosTipoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDocumentosTipos extends ManageRecords
{
    protected static string $resource = DocumentosTipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
