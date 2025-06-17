<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class OauthConnectPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static string $view = 'filament.pages.conectar-cobros';

    protected static ?string $title = 'Cobros';
    protected static ?string $navigationLabel = 'Cobros';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function getUser()
    {
        return Auth::user();
    }
}

