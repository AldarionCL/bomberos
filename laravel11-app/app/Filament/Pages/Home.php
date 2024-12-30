<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-viewfinder-circle';

    protected static string $view = 'filament.pages.home';

    protected static ?string $title = 'Noticias';

}
