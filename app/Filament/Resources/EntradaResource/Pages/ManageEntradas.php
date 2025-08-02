<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use Filament\Resources\Pages\Page;
use App\Models\Entrada;
use App\Models\Evento;
use App\Filament\Resources\EntradaResource;

class ManageEntradas extends Page
{
    public Evento $evento;
    public $entradas;

    protected static string $resource = EntradaResource::class;
    protected static string $view = 'filament.resources.entrada-resource.pages.manage-entradas';

    // Recibimos el slug como parámetro desde la URL
    public function mount($slug)
    {
        $this->evento = Evento::where('slug', $slug)->firstOrFail();
        $this->entradas = $this->evento->entradas;
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Crear Entrada / Ticket';
    }

    
}
