<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ResumenCuotas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.resumen-cuotas';
    protected static bool $shouldRegisterNavigation = false;

    public function mount($idUsuario){
        $this->idUsuario = $idUsuario;

    }
}
