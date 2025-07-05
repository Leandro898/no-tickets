<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class TicketScanner extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'Ticket Scanner';
    protected static ?string $slug = 'ticket-scanner';
    protected static ?int    $navigationSort  = 2;
    protected static string  $view = 'filament.pages.ticket-scanner';

    public static function shouldRegisterNavigation(): bool
    {
        // Sólo admins
        return auth()->check() && auth()->user()->hasRole('admin');
    }
}
