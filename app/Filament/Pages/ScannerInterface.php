<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerInterface extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scanner de Tickets';
    // protected static ?string $navigationGroup = 'Utilidades'; // Opcional
    protected static ?int $navigationSort = 2; // Orden en menú
    protected static string $view = 'filament.pages.scanner-interface';
}