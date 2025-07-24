<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Toggle;



class ListEventos extends ListRecords
{
    protected static string $resource = EventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear-evento')               // Nombre único
                ->label('Crear Evento')
                ->icon('heroicon-o-plus')
                ->modalHeading('¿Tendrá butacas numeradas?')
                // 1) Definimos el form del modal
                ->form([
                    Toggle::make('has_seats')
                        ->label('Usar butacas numeradas')
                        ->helperText('Marca para configurar butacas numeradas en este evento.')
                        ->default(false),
                ])
                // 2) Botones del modal
                ->modalActions([
                    Action::make('cancel')
                        ->label('Cancelar')
                        ->color('secondary')
                        ->close(),
                    Action::make('continue')
                        ->label('Continuar')
                        ->color('primary')
                        ->action(fn(array $data) => redirect(
                            EventoResource::getUrl('create', [
                                'has_seats' => ! empty($data['has_seats'] ?? false) ? 1 : 0,
                            ])
                        ))
                        ->close(),
                ]),
        ];
    }

    // PARA QUITAR MIGAS DE PAN
    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn($record) => EventoResource::getUrl('detalles', ['record' => $record]);
    }
}
