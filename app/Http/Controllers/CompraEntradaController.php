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
            // Validar datos generales
            $validatedData = $request->validate([
                'nombre' => 'required|string',
                'email' => 'required|email',
                'cantidades' => 'required|array|min:1', // Aseguramos que al menos una cantidad sea mayor a 0
                'cantidades.*' => 'integer|min:0', // Validamos que las cantidades sean enteros no negativos
            ]);

            DB::beginTransaction(); // Iniciamos una transacción para asegurar la integridad de la base de datos

            $totalEntradasCompradas = 0;
            $ticketsCreados = [];

            foreach ($validatedData['cantidades'] as $entradaId => $cantidad) {
                if ($cantidad > 0) {
                    $entrada = Entrada::findOrFail($entradaId);

                    if ($entrada->stock_actual < $cantidad) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "No hay suficiente stock disponible para '{$entrada->nombre}'. Stock actual: {$entrada->stock_actual}."]);
                    }

                    // Crear los tickets para esta entrada
                    for ($i = 0; $i < $cantidad; $i++) {
                        $ticket = Ticket::create([
                            'entrada_id' => $entrada->id,
                            'nombre' => $validatedData['nombre'],
                            'email' => $validatedData['email'],
                            'estado' => 'vendida',
                            'codigo_qr' => uniqid('QR-'),
                        ]);
                        $ticketsCreados[] = $ticket;
                        $totalEntradasCompradas++;
                    }

                    // Reducir el stock de la entrada
                    $entrada->decrement('stock_actual', $cantidad);
                }
            }

            if ($totalEntradasCompradas === 0) {
                throw ValidationException::withMessages(['cantidades' => 'Debes seleccionar al menos una entrada.']);
            }

            DB::commit(); // Si todo salió bien, confirmamos la transacción

            // Redirigir con éxito
            return redirect()->route('comprar.entrada', $evento->id)
                            ->with('success', '¡Compra realizada correctamente! Se han generado ' . $totalEntradasCompradas . ' entradas.');

        } catch (ValidationException $e) {
            DB::rollBack(); // Si hay errores de validación, revertimos la transacción
            return redirect()->back()->withErrors($e->validator->getMessageBag())->withInput();
        } catch (\Exception $e) {
            DB::rollBack(); // Si hay otros errores, también revertimos
            \Log::error('Error al comprar entrada: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Hubo un error al procesar tu compra.'])->withInput();
        }
    }

    public function index()
    {
        $tickets = Ticket::with('entrada.evento')->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }

}