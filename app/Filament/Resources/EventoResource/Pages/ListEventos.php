<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\BulkAction;
use App\Models\Evento;
use Closure;
use Illuminate\Support\Facades\Log;
use Filament\Actions;
use App\Filament\Resources\EventoResource\Widgets\CustomHeaderBox;
use Filament\Tables\Actions\Action;

class ListEventos extends ListRecords
{

    public function __construct()
    {
        Log::info('✅ Página ListEventos cargada');
    }

    protected static string $resource = EventoResource::class;

    public string $headerTestMessage = '¡Header cargado desde Livewire!';

    /**
     * Define la URL a donde ir al hacer clic en una fila
     */
    protected function getTableRecordUrlUsing(): ?\Closure
    {
        return fn($record) => EventoResource::getUrl('detalles', ['record' => $record->id]);
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

    public function getTitle(): string
    {
        return ''; // Retorna una cadena vacía para que no se muestre ningún título
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CustomHeaderBox::class, // Registra tu widget aquí para que aparezca en el encabezado
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Puedes añadir otros widgets aquí si los necesitas en el pie de la página
        ];
    }


    /**
     * (Opcional) quita cualquier botón en el header de la tabla
     */
    protected function getTableHeaderActions(): array
    {
        return [];
    }
}
