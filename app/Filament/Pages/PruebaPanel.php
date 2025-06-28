<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PruebaPanel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.prueba-panel';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationLabel(): string
    {
        return 'Prueba de Estilos';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-beaker'; // Icono de ejemplo, puedes cambiarlo
    }

    // Opcional: agrupar en el menú
    public static function getNavigationGroup(): ?string
    {
        return 'Utilidades'; // Cambia el texto a tu gusto
    }
}
