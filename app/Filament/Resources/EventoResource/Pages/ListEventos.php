<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use App\Models\Evento;
use Closure;
use Illuminate\Support\Facades\Log;
use Filament\Actions;

class ListEventos extends ListRecords
{   

    public function __construct()
    {
        Log::info('✅ Página ListEventos cargada');
    }

    protected static string $resource = EventoResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->label('Nombre'),
                TextColumn::make('fecha')->label('Fecha'),
                TextColumn::make('lugar')->label('Lugar'),
            ])
            ->actions([
                Action::make('ver_detalles')
                    ->label('Ver detalles')
                    ->url(fn ($record) => EventoResource::getUrl('detalles', ['record' => $record->id]))
                    ->icon('heroicon-o-eye'),
            ])
            ->recordUrl(fn (Evento $record) => EventoResource::getUrl('detalles', ['record' => $record->id])); // <- esta línea hace que toda la fila sea clickeable    
    }

    /**
     * Define la URL a donde ir al hacer clic en una fila
     */
    protected function getRecordUrlUsing(): ?Closure
    {
        //dd('Este método sí se está ejecutando');

        return fn (Evento $record): string => static::getResource()::getUrl('detalles', ['record' => $record]);
    }

    /**
     * Muestra el botón de acciones masivas antes de la paginación
     */
    public function getTableBulkActionsPosition(): string
    {
        return 'before-pagination';
    }

    /**
     * Hace que el botón de acciones masivas siempre se muestre
     */
    public function shouldRenderTableBulkActions(): bool
    {
        return true;
    }

    /**
     * Mensaje cuando no hay eventos
     */
    public function getTableEmptyStateHeading(): ?string
    {
        return 'No se encontraron eventos';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make() // El boton y el texto del boton se configuran desde aca porque parece que este archivo sobreescribre configuracion de EventoResourve
            ->label('Crear Evento'),
        ];
    }
}