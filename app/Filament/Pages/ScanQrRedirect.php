<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScanQrRedirect extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Escanear QR';
    protected static ?string $slug = 'escanear-qr';

    protected static string $view = 'filament.pages.scan-qr-redirect';

    public function mount(): void
    {
        redirect()->to(route('scanner.index'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Esto OCULTA el ítem en el menú
    }
}
