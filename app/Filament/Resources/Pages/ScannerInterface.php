<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ScannerInterface extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.resources.evento-resource.pages.scanner-interface'; // Esto carga tu Blade que luego carga el Livewire

    protected static ?string $title = 'Scanner de Tickets';
    protected static ?string $slug = 'scanner';
}