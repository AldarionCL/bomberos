<?php

namespace App\Filament\Resources\PersonasResource\Pages;

use App\Filament\Resources\PersonasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreatePersonas extends CreateRecord
{
    protected static string $resource = PersonasResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        if(!isset($data['password'])){
            $data['password'] = Hash::make('12345678'); // Default password if none provided
        }

        return $data;
    }
}
