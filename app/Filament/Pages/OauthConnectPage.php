<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OauthConnectPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.conectar-cobros';

    protected static ?string $title = 'Cobros';
    protected static ?string $navigationLabel = 'Cobros';
    protected static ?int $navigationSort = 3; // Cambia la posición en el menú si querés

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
