<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use App\Models\Documentos;
use App\Models\DocumentosTipo;
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

        $tipoDocs = DocumentosTipo::all()->pluck('Tipo', 'id');
        $tabs = ['Todos' => Tab::make()
            ->badge(fn()=>Documentos::count())
        ];
        if($tipoDocs) {
            foreach ($tipoDocs as $id => $tipoDoc) {
                $tabs[$tipoDoc] = Tab::make()
                    ->modifyQueryUsing(fn(Builder $query) => $query->where('TipoDocumento', $id))
                ->badge(fn()=>Documentos::where('TipoDocumento',$id)->count());
            }
        }
        return $tabs;
    }
}
