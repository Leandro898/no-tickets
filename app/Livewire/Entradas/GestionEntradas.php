<?php
//Path: app/Livewire/Entradas/GestionEntradas.php
// This file is part of the Livewire component for managing event entries.
namespace App\Livewire\Entradas;

use Livewire\Component;
use App\Models\Entrada;

class GestionEntradas extends Component
{
    public $evento_id;
    public $entradas = [];

    // Para el modal:
    public $showModal = false;
    public $entrada_id   = null;
    public $nombre       = '';
    public $precio       = null;
    public $stock_inicial = null;

    protected $rules = [
        'nombre'        => 'required|string|max:255',
        'precio'        => 'required|numeric|min:0',
        'stock_inicial' => 'required|integer|min:1',
    ];

    public function mount($evento_id)
    {
        $this->evento_id = $evento_id;
        $this->loadEntradas();
    }

    public function loadEntradas()
    {
        $this->entradas = Entrada::where('evento_id', $this->evento_id)->get();
    }

    public function openCreate()
    {
        $this->reset(['entrada_id', 'nombre', 'precio', 'stock_inicial']);
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $e = Entrada::findOrFail($id);
        $this->entrada_id    = $e->id;
        $this->nombre        = $e->nombre;
        $this->precio        = $e->precio;
        $this->stock_inicial = $e->stock_inicial;
        $this->showModal     = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['entrada_id', 'nombre', 'precio', 'stock_inicial']);
    }

    public function save()
    {
        $this->validate();

        if ($this->entrada_id) {
            // Editar
            Entrada::find($this->entrada_id)->update([
                'nombre'        => $this->nombre,
                'precio'        => $this->precio,
                'stock_inicial' => $this->stock_inicial,
            ]);
        } else {
            // Crear
            Entrada::create([
                'evento_id'      => $this->evento_id,
                'nombre'         => $this->nombre,
                'precio'         => $this->precio,
                'stock_inicial'  => $this->stock_inicial,
            ]);
        }

        $this->loadEntradas();
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.entradas.gestion-entradas');
    }
}
