<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EntradaResource;
use App\Filament\Resources\EventoResource;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;
use Filament\Forms\Actions;

class CreateEvento extends CreateRecord
{
    protected static string $resource = EventoResource::class;

    // PROPIEDAD PARA OVERRIDA BLADE
    //protected static string $view = 'vendor.filament-panels.pages.evento-create';

    protected function getRedirectUrl(): string
    {
        return EntradaResource::getUrl('manage-entradas', [
            // Aquí envías el slug del evento, no el evento_id
            'slug'      => $this->record->slug,
            // El has_seats irá como query string
            'has_seats' => $this->record->has_seats ? 1 : 0,
        ]);
    }



    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Evento';
    }

    public static function canCreateAnother(): bool
    {
        return false;
    }

    /**
     * Los "header actions" son los botones que aparecen junto al título.
     */
    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->label('Volver a Eventos')
                ->icon('heroicon-o-arrow-left')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    // /**
    //  * Aquí personalizamos el botón de Crear para que pida confirmación.
    //  */
    // protected function getFormActions(): array
    // {
    //     return [
    //         // Botón crear
    //         CreateAction::make('create')
    //             ->label('Crear Evento')
    //             ->createAnother(false),
                
    //     ];
    // }
}
