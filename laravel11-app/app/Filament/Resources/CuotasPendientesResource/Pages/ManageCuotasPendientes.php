<?php

namespace App\Filament\Resources\CuotasPendientesResource\Pages;

use App\Filament\Resources\CuotasPendientesResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCuotasPendientes extends ManageRecords
{
    protected static string $resource = CuotasPendientesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
