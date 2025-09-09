<?php
//Path: app/Livewire/Entradas/GestionEntradas.php
// Este archivo es parte del componente Livewire para gestionar las entradas de un evento.
namespace App\Livewire\Entradas;

use Livewire\Component;
use App\Models\Entrada;

class GestionEntradas extends Component
{
    public $evento_id;
    public $entradas = [];

    // Para el modal:
    public $showModal = false;
    public $entrada_id      = null;
    public $nombre          = '';
    public $precio          = null;
    public $stock_inicial   = null;
    public $stock_a_agregar = null; // Nuevo campo para agregar stock

    protected function rules()
    {
        $rules = [
            'nombre'        => 'required|string|max:255',
            'precio'        => 'required|numeric|min:0',
            'stock_inicial' => 'required|integer|min:1',
        ];

        // Añadir una regla de validación para 'stock_a_agregar' solo si estamos en modo de edición
        if ($this->entrada_id) {
            $rules['stock_a_agregar'] = 'nullable|integer|min:0';
        }

        return $rules;
    }

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
        $this->reset(['entrada_id', 'nombre', 'precio', 'stock_inicial', 'stock_a_agregar']);
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $e = Entrada::findOrFail($id);
        $this->entrada_id      = $e->id;
        $this->nombre          = $e->nombre;
        $this->precio          = $e->precio;
        $this->stock_inicial   = $e->stock_inicial;
        $this->stock_a_agregar = null; // Inicializar en null cada vez que se abre el modal de edición
        $this->showModal       = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['entrada_id', 'nombre', 'precio', 'stock_inicial', 'stock_a_agregar']);
    }

    public function save()
    {
        $this->validate();

        if ($this->entrada_id) {
            // Lógica para editar
            $entrada = Entrada::find($this->entrada_id);

            // Si hay stock para agregar, actualizamos ambos campos de stock
            if ($this->stock_a_agregar > 0) {
                $entrada->stock_inicial += $this->stock_a_agregar;
                $entrada->stock_actual  += $this->stock_a_agregar;
            }

            // Actualizar los otros campos
            $entrada->nombre = $this->nombre;
            $entrada->precio = $this->precio;
            $entrada->save();
        } else {
            // Lógica para crear
            Entrada::create([
                'evento_id'     => $this->evento_id,
                'nombre'        => $this->nombre,
                'precio'        => $this->precio,
                'stock_inicial' => $this->stock_inicial,
                'stock_actual'  => $this->stock_inicial, // Asegurarse de que el stock actual se inicialice
            ]);
        }

        $this->loadEntradas();
        $this->closeModal();
    }

    public function restock(int $id)
    {
        $entrada = Entrada::findOrFail($id);

        // Asumimos que reponer stock significa restaurar al stock inicial
        // O podrías definir una cantidad fija para reponer
        $cantidad_a_reponer = $entrada->stock_inicial - $entrada->stock_actual;

        if ($cantidad_a_reponer > 0) {
            $entrada->stock_actual += $cantidad_a_reponer;
            $entrada->save();
        }

        $this->loadEntradas();
        $this->dispatch('message', 'Stock repuesto con éxito.');
    }

    public function render()
    {
        return view('livewire.entradas.gestion-entradas');
    }
}
