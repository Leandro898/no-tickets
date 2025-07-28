<?php

namespace App\Filament\Resources\EntradaResource\Pages;

use Filament\Resources\Pages\Page;
use App\Models\Entrada;
use App\Models\Evento;
use App\Filament\Resources\EntradaResource;

class ManageEntradas extends Page
{
    public $evento_id;
    public $evento;
    public $entradas;

    protected static string $resource = EntradaResource::class;
    protected static string $view = 'filament.resources.entrada-resource.pages.manage-entradas';

    public function mount()
    {
        $this->evento_id = request()->query('evento_id');
        $this->evento = Evento::findOrFail($this->evento_id);
        $this->entradas = Entrada::where('evento_id', $this->evento_id)->get();
    }

    // QUITAR MIGAS DE PAN
    public function getBreadcrumbs(): array
    {
        return [];
    }

    // Quitar titulo de la vista
    public function getTitle(): string
    {
        return 'Crear Entrada / Ticket';
    }
}
