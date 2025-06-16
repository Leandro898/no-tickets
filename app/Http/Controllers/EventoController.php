<?php

namespace App\Http\Controllers;

use App\Models\Evento; // Importa tu modelo Evento
use Illuminate\Http\Request; // Importa Request si lo vas a usar en otros métodos

class EventoController extends Controller
{
    /**
     * Muestra una lista de todos los eventos.
     * (Puedes implementar esto si tienes una página de listado de eventos)
     */
    public function index()
    {
        $eventos = Evento::all();
        return view('eventos.index', compact('eventos'));
    }

    /**
     * Muestra el formulario para crear un nuevo evento.
     * (Si lo manejas con Filament, este método podría no ser necesario para el público)
     */
    public function create()
    {
        // return view('eventos.create');
    }

    /**
     * Almacena un nuevo evento en la base de datos.
     * (Si lo manejas con Filament, este método podría no ser necesario para el público)
     */
    public function store(Request $request)
    {
        // Lógica para guardar un evento
        // $validatedData = $request->validate([...]);
        // Evento::create($validatedData);
        // return redirect()->route('eventos.index')->with('success', 'Evento creado.');
    }

    /**
     * Muestra los detalles de un evento específico.
     * Esta es la función CLAVE para nuestro flujo.
     */
    public function show(Evento $evento)
    {
        // El Route Model Binding de Laravel inyecta automáticamente el objeto Evento
        // Carga la relación 'entradas' para evitar el problema N+1 al acceder a $evento->entradas
        // Esto asegura que todas las entradas del evento se carguen en una sola consulta.
        $evento->load('entradas');

        // Retorna la vista 'eventos.show' y le pasa el objeto $evento
        return view('eventos.show', compact('evento'));
    }

    /**
     * Muestra el formulario para editar un evento existente.
     * (Si lo manejas con Filament, este método podría no ser necesario para el público)
     */
    public function edit(Evento $evento)
    {
        // return view('eventos.edit', compact('evento'));
    }

    /**
     * Actualiza un evento existente en la base de datos.
     * (Si lo manejas con Filament, este método podría no ser necesario para el público)
     */
    public function update(Request $request, Evento $evento)
    {
        // Lógica para actualizar un evento
        // $validatedData = $request->validate([...]);
        // $evento->update($validatedData);
        // return redirect()->route('eventos.show', $evento)->with('success', 'Evento actualizado.');
    }

    /**
     * Elimina un evento de la base de datos.
     * (Si lo manejas con Filament, este método podría no ser necesario para el público)
     */
    public function destroy(Evento $evento)
    {
        // Lógica para eliminar un evento
        // $evento->delete();
        // return redirect()->route('eventos.index')->with('success', 'Evento eliminado.');
    }
}
