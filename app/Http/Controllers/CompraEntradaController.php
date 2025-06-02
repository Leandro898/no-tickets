<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Entrada;
use App\Models\Order;
use App\Models\PurchasedTicket; // Aunque los crea el webhook, la referencia es útil
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

// Importa el MercadoPagoController para poder usarlo
use App\Http\Controllers\MercadoPagoController;

class CompraEntradaController extends Controller
{
    protected $mercadopagoController;

    public function __construct(MercadoPagoController $mercadopagoController)
    {
        $this->mercadopagoController = $mercadopagoController;
    }

    /**
     * Muestra el formulario de compra de entradas para un evento específico.
     * @param Evento $evento
     * @return \Illuminate\View\View
     */
    public function show(Evento $evento)
    {
        $now = Carbon::now();
        $entradas = $evento->entradas()
            ->where('stock_actual', '>', 0)
            ->where(function ($query) use ($now) {
                $query->whereNull('disponible_desde')
                      ->orWhere('disponible_desde', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('disponible_hasta')
                      ->orWhere('disponible_hasta', '>=', $now);
            })
            ->get();

        return view('comprar', compact('evento', 'entradas'));
    }

    /**
     * Procesa el formulario de compra, crea la orden y delega la creación de la preferencia a MercadoPagoController.
     * @param Request $request
     * @param Evento $evento
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Evento $evento)
    {
        try {
            $validatedData = $request->validate([
                'nombre' => 'required|string',
                'email' => 'required|email',
                'cantidades' => 'required|array|min:1',
                'cantidades.*' => 'integer|min:0',
                'buyer_phone' => 'nullable|string|max:50',
                'buyer_dni' => 'nullable|string|max:20',
            ]);

            $totalAmount = 0;
            $itemsForMercadoPago = [];
            $entradasSeleccionadas = [];

            foreach ($validatedData['cantidades'] as $entradaId => $cantidad) {
                if ($cantidad > 0) {
                    $entrada = Entrada::findOrFail($entradaId);

                    if ($entrada->stock_actual < $cantidad) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "No hay suficiente stock disponible para '{$entrada->nombre}'. Stock actual: {$entrada->stock_actual}."]);
                    }
                    if ($entrada->max_por_compra && $cantidad > $entrada->max_por_compra) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "No puedes comprar más de " . $entrada->max_por_compra . " entradas de tipo '{$entrada->nombre}'."]);
                    }

                    $now = Carbon::now();
                    if ($entrada->disponible_desde && $now->lt($entrada->disponible_desde)) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "La venta para '{$entrada->nombre}' aún no ha comenzado."]);
                    }
                    if ($entrada->disponible_hasta && $now->gt($entrada->disponible_hasta)) {
                        throw ValidationException::withMessages(['cantidades.' . $entradaId => "La venta para '{$entrada->nombre}' ha finalizado."]);
                    }

                    $subtotal = $entrada->precio * $cantidad;
                    $totalAmount += $subtotal;

                    $itemsForMercadoPago[] = [
                        "title" => $entrada->nombre . ' - ' . $evento->nombre,
                        "quantity" => (int) $cantidad,
                        "unit_price" => (float) $entrada->precio,
                        "currency_id" => 'ARS',
                        "id" => (string) $entrada->id,
                    ];

                    $entradasSeleccionadas[] = [
                        'entrada_id' => $entrada->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $entrada->precio,
                    ];
                }
            }

            if ($totalAmount === 0) {
                throw ValidationException::withMessages(['cantidades' => 'Debes seleccionar al menos una entrada.']);
            }

            DB::beginTransaction();

            $order = Order::create([
                'event_id' => $evento->id,
                'buyer_full_name' => $validatedData['nombre'],
                'buyer_email' => $validatedData['email'],
                'buyer_phone' => $request->input('buyer_phone'),
                'buyer_dni' => $request->input('buyer_dni'),
                'total_amount' => $totalAmount,
                'payment_status' => 'pending',
                'items_data' => json_encode($entradasSeleccionadas),
            ]);

            $backUrls = [
                "success" => route('purchase.success', ['order' => $order->id]),
                "failure" => route('purchase.failure', ['order' => $order->id]),
                "pending" => route('purchase.pending', ['order' => $order->id]),
            ];

            $payer = [
                "email" => $validatedData['email'],
                "name" => $validatedData['nombre'],
                "phone" => [
                    "number" => $validatedData['buyer_phone'],
                ],
                // Puedes añadir más datos del pagador si los tienes
                "identification" => ["type" => "DNI", "number" => $validatedData['buyer_dni']],
            ];

            // *** DELEGACIÓN: Llamar al MercadoPagoController para crear la preferencia ***
            $preference = $this->mercadopagoController->createPreference(
                $itemsForMercadoPago,
                $payer,
                (string) $order->id, // external_reference
                $backUrls
            );

            $order->mp_preference_id = $preference->id;
            $order->save();

            DB::commit();

            return redirect()->away($preference->init_point);

        } catch (ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withErrors($e->validator->getMessageBag())->withInput();
        } catch (MPApiException $e) {
            DB::rollBack();
            $apiResponseContent = $e->getApiResponse()->getContent();
            $apiStatusCode = $e->getApiResponse()->getStatusCode();

            Log::error('Error de API al iniciar compra de entradas (delegado a MP): Status ' . $apiStatusCode . ' - Content: ' . json_encode($apiResponseContent));

            $errorMessage = 'Error de API al procesar tu compra: ';
            if (isset($apiResponseContent['message'])) {
                $errorMessage .= $apiResponseContent['message'];
            } elseif (isset($apiResponseContent['error_description'])) {
                $errorMessage .= $apiResponseContent['error_description'];
            } else {
                $errorMessage .= 'Status ' . $apiStatusCode . ' - Error desconocido.';
            }
            return redirect()->back()->withErrors(['error' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error general al iniciar compra de entradas: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Hubo un error al procesar tu compra. Por favor, inténtelo de nuevo.'])->withInput();
        }
    }

    // --- Métodos de gestión de tickets (para tu vista de tickets comprados) ---
    public function index()
    {
        $tickets = PurchasedTicket::with('entrada.evento')->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }
}