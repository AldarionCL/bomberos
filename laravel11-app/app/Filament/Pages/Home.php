<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Noticias';
    protected static ?string $title = 'Noticias';
    protected static ?int $navigationSort = -1;

    protected static string $view = 'filament.pages.home';
}
