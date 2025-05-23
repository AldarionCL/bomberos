<?php

namespace App\Filament\Resources\DocumentosResource\Pages;

use App\Filament\Resources\DocumentosResource;
use App\Models\Noticias;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CreateDocumentos extends CreateRecord
{
    protected static string $resource = DocumentosResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($data["Noticia"]) {
            Noticias::create([
                "Titulo" => "Nuevo documento publicado",
                "SubTitulo" => $data["TipoDocumento"],
                "Contenido" => "<p>Se ha publicado un nuevo documento, puede descargarlo en el siguiente enlace:</p>
                <a href='" . Storage::disk('public')->download($data['Path']) . "'>Descargar</a>",
                "FechaPublicacion" => Carbon::today()->format("Y-m-d"),
                "createdBy" => Auth::user()->id,

            ]);
        }

        if($data["AsociadoA"]) {
            $usuarios = User::find($data["AsociadoA"]);
        }else{
            $usuarios = User::all();
        }

        Notification::make()
            ->title('Nuevo documento publicado')
            ->body('Se ha publicado un nuevo documento en el sistema. "' . $data["Nombre"] .'"')
            ->actions([
                    Action::make('Descargar')
                    ->url(Storage::disk('public')->url($data["Path"]))
                ]
            )
            ->success()
            ->sendToDatabase($usuarios);


        return $data;
    }


}
