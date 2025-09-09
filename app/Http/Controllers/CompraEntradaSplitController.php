<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Order;
use App\Models\Entrada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use Exception;
use MercadoPago\Exceptions\MPApiException;
use App\Http\Controllers\MercadoPagoController;

class CompraEntradaSplitController extends Controller
{
    /**
     * Paso 1: muestro la vista donde el usuario elige cantidad.
     */
    public function show(Evento $evento)
    {
        $evento->load('entradas');
        return view('comprar-entrada-split', compact('evento'));
    }

    /**
     * Paso 1 (POST): recibo entrada_id + cantidad, los valido y guardo en sesión.
     */
    public function store(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'entrada_id' => 'required|integer|exists:entradas,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        // Compruebo que la entrada pertenezca al evento y tenga stock
        $entrada = $evento->entradas()->findOrFail($data['entrada_id']);
        if ($data['cantidad'] > $entrada->stock_actual) {
            return back()
                ->withErrors(['cantidad' => 'No hay stock suficiente.'])
                ->withInput();
        }

        // Guardo en sesión para el siguiente paso
        session([
            'compra.entrada_id' => $entrada->id,
            'compra.cantidad' => $data['cantidad'],
        ]);

        // Redirijo al formulario de datos del comprador
        return redirect()->route('eventos.comprar.split.showDatos', $evento);
    }

    /**
     * Paso 2: muestro el formulario de datos del comprador,
     * precargando la entrada y cantidad seleccionadas.
     */
    public function showDatos(Evento $evento)
    {
        $evento->load('entradas');

        $entradaId = session('compra.entrada_id');
        $cantidad = session('compra.cantidad', 1);

        if (!$entradaId) {
            return redirect()
                ->route('eventos.comprar.split', $evento)
                ->withErrors('Debes seleccionar una entrada primero.');
        }

        $entrada = $evento->entradas->firstWhere('id', $entradaId);
        $subtotal = $entrada->precio * $cantidad;

        return view('comprar-entrada-datos', compact('evento', 'entrada', 'cantidad', 'subtotal'));
    }

    /**
     * Paso 2 (POST): recibo los datos personales, creo la orden
     * y genero la preferencia de Mercado Pago.
     */
    public function storeDatos(Request $request)
    {
        $datosCompra = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);
        $request->session()->put('datosCompra', $datosCompra);

        $compra = $request->session()->get('compra');

        if (!$compra || !$datosCompra) {
            return redirect()->route('home')->withErrors(['error' => 'No se encontraron datos de compra en la sesión.']);
        }

        // Usamos una transacción para garantizar la integridad de los datos
        DB::beginTransaction();

        try {
            $entrada = Entrada::lockForUpdate()->find($compra['entrada_id']);

            if (!$entrada) {
                // Si la entrada no existe o ha sido eliminada.
                throw new Exception('La entrada seleccionada no es válida.');
            }

            // **MODIFICACIÓN CLAVE**: Volvemos a validar el stock justo antes de descontarlo.
            // Esto previene race conditions (múltiples usuarios comprando al mismo tiempo).
            if ($compra['cantidad'] > $entrada->stock_actual) {
                throw new Exception('No hay stock suficiente disponible en este momento. Por favor, intente con una cantidad menor.');
            }

            // **MODIFICACIÓN CLAVE**: Descontamos el stock actual de la entrada
            $entrada->stock_actual -= $compra['cantidad'];
            $entrada->save();

            // Continuamos con la lógica de creación de la orden
            $subtotal = $entrada->precio * $compra['cantidad'];

            $entrada->load('evento.organizador');

            if (!$entrada || !$entrada->evento || !$entrada->evento->organizador) {
                throw new Exception('No se pudo procesar la compra.');
            }

            $vendorAccessToken = $entrada->evento->organizador->getMercadoPagoAccessToken();
            $vendorId = $entrada->evento->organizador->mp_user_id;
            $commission = $subtotal * config('mercadopago.platform_fee_percentage');

            $order = Order::create([
                'buyer_full_name' => $datosCompra['nombre'],
                'buyer_email' => $datosCompra['email'],
                'subtotal' => $subtotal,
                'commission' => $commission,
                'total_amount' => $subtotal,
                'event_id' => $entrada->evento_id,
                'seller_user_id' => $entrada->evento->organizador->id,
                'payment_status' => 'pending',
                'items_data' => json_encode([
                    [
                        'entrada_id' => $entrada->id,
                        'cantidad' => $compra['cantidad'],
                        'nombre' => $entrada->nombre,
                        'precio' => $entrada->precio,
                    ],
                ]),
            ]);

            $items = [
                [
                    'title' => $entrada->nombre,
                    'quantity' => (int)$compra['cantidad'],
                    'unit_price' => (float)$entrada->precio,
                ],
            ];

            $payer = [
                'email' => $datosCompra['email'],
                'name' => $datosCompra['nombre'],
                'identification' => [
                    'type' => 'DNI',
                    'number' => '',
                ],
            ];

            $backUrls = [
                'success' => route('purchase.success', ['order' => $order->id]),
                'failure' => route('purchase.failure', ['order' => $order->id]),
                'pending' => route('purchase.pending', ['order' => $order->id]),
            ];

            $preference = (new MercadoPagoController())->createPreference(
                $vendorAccessToken,
                $items,
                $payer,
                $order->id,
                $backUrls,
                $commission,
                $subtotal
            );

            DB::commit(); // Confirmar la transacción
            $request->session()->forget(['compra', 'datosCompra']);

            return redirect()->away($preference->init_point);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción en caso de error
            Log::error('Error en el proceso de compra: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.']);
        }
    }
}
