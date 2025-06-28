<?php

namespace App\Filament\Pages;

use ArielMejiaDev\FilamentPrintable\Actions\PrintAction;
use Filament\Pages\Page;
use Torgodly\Html2Media\Actions\Html2MediaAction;

class ComprobantePago extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.comprobante-pago';

    public $record;
    public $cuota;

    public function getHeaderActions(): array
    {
        return [
            PrintAction::make()
        ];
    }
}
