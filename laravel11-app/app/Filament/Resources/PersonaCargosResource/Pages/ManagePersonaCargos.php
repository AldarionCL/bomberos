<?php

namespace App\Filament\Resources\PersonaCargosResource\Pages;

use App\Filament\Resources\PersonaCargosResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePersonaCargos extends ManageRecords
{
    protected static string $resource = PersonaCargosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
