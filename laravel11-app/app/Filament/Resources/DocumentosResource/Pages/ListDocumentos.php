<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDocumentos extends ListRecords
{
    protected static string $resource = DocumentosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Libres' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('AsociadoA')
                    ->whereNull('idSolicitud')),

            'Asociados' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('AsociadoA')),

        ];
    }
}
