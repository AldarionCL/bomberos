<?php

namespace App\Filament\Resources\TesoreriaResource\Pages;

use App\Filament\Resources\TesoreriaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTesoreria extends EditRecord
{
    protected static string $resource = TesoreriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
