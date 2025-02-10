<?php

namespace App\Filament\App\Themes;

use Filament\Panel;
use Hasnayeen\Themes\Contracts\CanModifyPanelConfig;
use Hasnayeen\Themes\Contracts\Theme;

class Bomberos implements CanModifyPanelConfig, Theme
{
    public static function getName(): string
    {
        return 'bomberos';
    }

    public static function getPath(): string
    {
        return 'resources/css/filament/admin/themes/bomberos.css';
    }

    public function getThemeColor(): array
    {
        return [
            'primary' => '#ffffff',
        ];
    }

    public function modifyPanelConfig(Panel $panel): Panel
    {
        return $panel
            ->viteTheme($this->getPath());
    }
}
