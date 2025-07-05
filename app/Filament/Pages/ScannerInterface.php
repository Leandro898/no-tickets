<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerInterface extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scanner de Tickets';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.scanner-interface';

    // ESTE METODO QUITA LA ADHESION AUTOMATICA DE ESTE ARCHIVO PAGE DE FILAMENT. TODOS LOS ARCHIVOS QUE SE CREAN DENTRO DE app/Filament/Pages SE COLOCAN AUTOMATICAMENTE EN LA NAVEGACION DE LA IZQUIERDA
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
