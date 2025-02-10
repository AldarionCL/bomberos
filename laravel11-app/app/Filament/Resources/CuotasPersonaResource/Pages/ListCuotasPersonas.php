<?php

namespace App\Filament\Resources\CuotasPersonaResource\Pages;

use App\Filament\Resources\CuotasPersonaResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCuotasPersonas extends ListRecords
{
    protected static string $resource = CuotasPersonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
//            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Activos' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Activo', 1)),
            'Inactivos' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Activo', '<>', 1)),
        ];
    }
}
