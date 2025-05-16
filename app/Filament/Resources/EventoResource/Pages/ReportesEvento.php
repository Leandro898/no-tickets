<?php

namespace App\Filament\Resources\EventoResource\Pages;

use App\Filament\Resources\EventoResource;
use App\Models\Evento; // Importa tu modelo específico
use Filament\Resources\Pages\Page;

class ReportesEvento extends Page
{
    protected static string $resource = EventoResource::class;
    protected static string $view = 'filament.resources.evento-resource.pages.reportes-evento';
    
    protected ?string $model = Evento::class; // Define el modelo concreto
    
    public Evento $record; // Usa el tipo específico
    
    public function mount(Evento $record): void // Tipo específico en el parámetro
    {
        $this->record = $record;
    }
    
    public function getRecord(): Evento
    {
        return $this->record;
    }
}