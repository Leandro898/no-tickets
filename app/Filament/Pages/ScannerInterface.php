<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerInterface extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scanner de Tickets';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view            = 'filament.pages.scanner-interface';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
}
