<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Entrada;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompraEntradaController extends Controller
{
    public function show(Evento $evento)
    {
        // Pasamos el evento y sus entradas para que el usuario elija
        $entradas = $evento->entradas()->where('stock_actual', '>', 0)->get();

        return view('comprar', compact('evento', 'entradas'));
    }

    public function store(Request $request, Evento $evento)
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'nombre' => 'required|string',
                'email' => 'required|email',
                'entrada_id' => 'required|exists:entradas,id',
            ]);

            // Obtener entrada
            $entrada = Entrada::findOrFail($validated['entrada_id']);

            // Verificar stock
            if ($entrada->stock_actual <= 0) {
                return redirect()->back()
                                ->withErrors(['entrada_id' => 'No hay stock disponible para esta entrada.']);
            }

            // Crear el ticket
            Ticket::create([
                'entrada_id' => $entrada->id,
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'estado' => 'vendida',
                'codigo_qr' => uniqid('QR-'),
            ]);

            // Reducir stock
            $entrada->decrement('stock_actual');

            // Redirigir con éxito
            return redirect()->route('comprar.entrada', $evento->id)
                            ->with('success', '¡Entrada comprada correctamente!');

        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator->getMessageBag());
        } catch (\Exception $e) {
            \Log::error('Error al comprar entrada: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Hubo un error al procesar tu compra.']);
        }
    }

    public function index()
    {
        $tickets = Ticket::with('entrada.evento')->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }

}