<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use App\Models\Noticias;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateDocumentos extends CreateRecord
{
    protected static string $resource = DocumentosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {


        if($data["Noticia"]){
            Noticias::create([
                "Titulo" => "Nuevo documento publicado",
                "SubTitulo" => $data["TipoDocumento"],
                "Contenido" => "<p>Se ha publicado un nuevo documento, puede descargarlo en el siguiente enlace:</p>
                <a href='".Storage::disk('public')->download($data['Path'])."'>Descargar</a>",
                "FechaPublicacion" => Carbon::today()->format("Y-m-d"),
                "createdBy" => Auth::user()->id,

            ]);
        }

        return $data;
    }

}
