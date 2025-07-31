<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use Filament\Resources\Pages\Page;               // ← fíjate bien aquí
use Illuminate\Contracts\View\View;

class ConfigureSeats extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view     = 'filament.pages.evento.configure-seats';

    public $record;
    public int $entriesCount = 0;

    public function mount($record): void
    {
        $this->record = EventoResource::getModel()::findOrFail($record);
        $this->entriesCount = $this->record
            ->entradas()
            ->sum('stock_actual');
    }

    public function getTitle(): string
    {
        return 'Configurar Mapa de Asientos';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    // App\Filament\Resources\EventoResource\Pages\ConfigureSeats.php

    public function getViewData(): array
    {
        return [
            'evento' => $this->record, // Así accedés al modelo completo
            'entriesCount' => $this->entriesCount,
        ];
    }
}
