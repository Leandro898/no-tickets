<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages; // Necesaria para el discoverPages
use Filament\Panel; // Necesaria para el tipo del método panel()
use Filament\PanelProvider; // Necesaria para extender PanelProvider
use Filament\Support\Colors\Color; // Necesaria para Color::Amber
use Filament\Widgets; // Si usas Widgets\AccountWidget::class
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Facades\FilamentAsset; // Si usas FilamentAsset
use Filament\Support\Assets\Css; // Si usas Css
use App\Filament\Resources\EventoResource;
use Filament\Navigation\NavigationBuilder; // Para el closure del navigation()
use Filament\Navigation\NavigationItem; // Para NavigationItem::make()
use App\Filament\Pages\ScannerInterface; // Para ScannerInterface::class
use App\Filament\Pages\OauthConnectPage;
use App\Filament\Pages\ScanQrPage;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
      	     
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->darkMode(false) // deshabilita el modo oscuro
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                ScannerInterface::class, // Registro explícito de la página del escáner
                OauthConnectPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
              	\App\Filament\Widgets\FloatingMenu::class,
                \Filament\Widgets\AccountWidget::class, // Usando el namespace completo para evitar ambigüedades
                \Filament\Widgets\FilamentInfoWidget::class, // Usando el namespace completo
            ])
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder->items([
                    ...EventoResource::getNavigationItems(), // Carga ítems del recurso Evento
                    // **** ALTERNATIVA "HARDCODEADA" SI LAS ANTERIORES FALLAN ****
                    NavigationItem::make('Scanner de Tickets') // Título fijo
                        ->url('/scanner-test')
                        ->icon('heroicon-o-qr-code') // Icono fijo
                        ->sort(2),
                    NavigationItem::make('Cobros')
                        ->url(OauthConnectPage::getUrl())
                        ->icon('heroicon-o-banknotes')
                        ->group('Cuenta')
                        ->sort(3),
                ]);
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \Spatie\Permission\Middleware\RoleMiddleware::class . ':admin|productor', // Esta línea es clave
            ])
            //->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName('Innova Ticket');
    }

    public function boot(): void
    {
        // 1) Orbital Menu
        FilamentView::registerRenderHook(
            'panels::body.end',
            fn () => view('components.floating-menu')
        );

        // 2) Registrar tu logo-mobile al inicio de la topbar
        FilamentView::registerRenderHook(
            'panels::topbar.start',
            fn () => view('components.logo-mobile')
        );
    }
}
