<?php

namespace App\Filament\Resources\CuotasPersonaResource\Pages;

use App\Filament\Resources\CuotasPersonaResource;
use App\Models\Cuota;
use Filament\Resources\Pages\Page;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class Comprobante extends Page
{
    protected static string $resource = CuotasPersonaResource::class;
    protected static ?string $title = '';

    public $record;
    public $cuota;

    protected static string $view = 'filament.resources.cuotas-persona-resource.pages.comprobante';

    public function mount($record)
    {
        $this->record = $record;
        $this->cuota = Cuota::find($record);
    }


    public function getHeaderActions(): array
    {
        return [

        ];
    }

}
