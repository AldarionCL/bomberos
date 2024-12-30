<?php

namespace App\Filament\Resources\NoticiasResource\Pages;

use App\Filament\Resources\NoticiasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateNoticias extends CreateRecord
{
    protected static string $resource = NoticiasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['createdBy'] = Auth::user()->id;
        return $data;
    }
}
