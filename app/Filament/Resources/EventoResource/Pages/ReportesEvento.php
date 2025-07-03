<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento; // Importa tu modelo específico
use App\Models\PurchasedTicket;
use Filament\Resources\Pages\Page;

class ReportesEvento extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view = 'filament.resources.evento-resource.pages.reportes-evento';

    public Evento $record;

    public int $qrsGenerados = 0;
    public int $qrsEscaneados = 0;

    public function mount(Evento $record): void
    {
        $this->record = $record;

        // Total QR generados (todos los tickets del evento)
        $this->qrsGenerados = \App\Models\PurchasedTicket::whereHas('entrada', function ($query) {
            $query->where('evento_id', $this->record->id);
        })->count();

        // QR escaneados = tickets con status 'used'
        $this->qrsEscaneados = \App\Models\PurchasedTicket::whereHas('entrada', function ($query) {
            $query->where('evento_id', $this->record->id);
        })->where('status', 'used')->count();
    }
    
    public function getRecord(): Evento
    {
        return $this->record;
    }

    public function getTitle(): string
    {
        return ''; // Retorna una cadena vacía para que no se muestre ningún título
    }
}