<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerInterface extends Page
{
    //protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    // ESTA ES LA RUTA EXACTA DE TU VISTA BLADE:
    protected static string $view = 'filament.resources.evento-resource.pages.scanner-interface';
    protected static ?string $title = 'Scanner de Tickets';
    protected static ?string $slug = 'scanner';

    // NO deben estar estas líneas aquí:
    // protected static ?int $navigationSort = 2;
    // protected static ?string $navigationGroup = null;
}