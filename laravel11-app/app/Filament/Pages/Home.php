<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Home extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Inicio';
    protected static ?string $title = 'Inicio';
    protected static ?int $navigationSort = -10;

    protected static string $view = 'filament.pages.home';
}
