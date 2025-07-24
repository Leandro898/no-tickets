<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Toggle;
use function redirect;



class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear-normal')
                ->label('Crear evento sin butacas')
                ->icon('heroicon-o-calendar')
                ->url(fn() => EventoResource::getUrl('create', [
                    'has_seats' => 0,
                ])),

            Action::make('crear-con-butacas')
                ->label('Crear evento con butacas')
                ->icon('heroicon-o-ticket')
                ->url(fn() => EventoResource::getUrl('create', [
                    'has_seats' => 1,
                ])),
        ];
    }

    // PARA QUITAR MIGAS DE PAN
    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn($record) => EventoResource::getUrl('detalles', [
            'record' => $record->getKey(), // o $record->id
        ]);
    }
}
