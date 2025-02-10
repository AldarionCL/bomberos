<?php

namespace App\Filament\Resources\UserRolesResource\Pages;

use App\Filament\Resources\UserRolesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserRoles extends EditRecord
{
    protected static string $resource = UserRolesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
