<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScanQrPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Scanner de Tickets';
    protected static ?string $slug = 'scanner';
    protected static string $view = 'filament.pages.scan-qr-page';
}
