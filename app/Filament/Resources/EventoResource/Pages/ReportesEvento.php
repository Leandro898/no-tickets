<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento;
use Filament\Resources\Pages\Page;
use Filament\Pages\Actions\Action;

class ReportesEvento extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view     = 'filament.resources.evento-resource.pages.reportes-evento';

    public Evento $record;
    public int    $qrsGenerados = 0;
    public int    $qrsEscaneados = 0;

    public function mount(Evento $record): void
    {
        $this->record        = $record;
        $this->qrsGenerados  = \App\Models\PurchasedTicket::whereHas('entrada', fn($q) => $q->where('evento_id', $record->id))->count();
        $this->qrsEscaneados = \App\Models\PurchasedTicket::whereHas('entrada', fn($q) => $q->where('evento_id', $record->id))
                                      ->where('status', 'used')
                                      ->count();
    }

    public function getRecord(): Evento
    {
        return $this->record;
    }

    public function getTitle(): string
    {
        return ''; 
    }

}
