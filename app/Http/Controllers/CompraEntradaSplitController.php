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
        // **CORRECCIÓN CLAVE:** Validar y guardar los datos del formulario en la sesión
        // Se cambió 'name' por 'nombre' en la validación
        $datosCompra = $request->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
        ]);
        $request->session()->put('datosCompra', $datosCompra);

        // Código para procesar el formulario y crear la orden
        $compra = $request->session()->get('compra');

        if (!$compra || !$datosCompra) {
            return redirect()->route('home')->withErrors(['error' => 'No se encontraron datos de compra en la sesión.']);
        }

        // Calcular el subtotal de la compra (precio entrada * cantidad)
        $entrada = Entrada::find($compra['entrada_id']);
        if (!$entrada) {
            return redirect()->back()->withErrors(['error' => 'La entrada seleccionada no es válida.']);
        }

        $subtotal = $entrada->precio * $compra['cantidad'];

        // Obtener la entrada del evento para saber el ID del vendedor
        // Ahora cargamos la relación correcta, 'organizador'.
        $entrada = Entrada::with('evento.organizador')->find($compra['entrada_id']);

        if (!$entrada || !$entrada->evento || !$entrada->evento->organizador) {
            return redirect()->back()->withErrors(['error' => 'No se pudo procesar la compra.']);
        }

        // Usamos tu método getter para obtener el access token del vendedor
        $vendorAccessToken = $entrada->evento->organizador->getMercadoPagoAccessToken();
        $vendorId = $entrada->evento->organizador->mp_user_id;

        // Calcular la comisión de la plataforma
        $commission = $subtotal * config('mercadopago.platform_fee_percentage');

        // Crear la orden en la base de datos
        $order = Order::create([
            'buyer_full_name' => $datosCompra['nombre'], // Se cambió aquí también para que coincida
            'buyer_email' => $datosCompra['email'],
            'subtotal' => $subtotal,
            'commission' => $commission,
            // CORRECCIÓN: El campo en la base de datos se llama 'total_amount', no 'final_amount'.
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

        // Preparar los datos de la preferencia
        $items = [
            [
                'title' => $entrada->nombre,
                'quantity' => (int)$compra['cantidad'],
                'unit_price' => (float)$entrada->precio,
            ],
        ];

        $payer = [
            'email' => $datosCompra['email'],
            'name' => $datosCompra['nombre'], // Y aquí
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

        try {
            // CORRECCIÓN CLAVE: Usamos el método `createPreference` del controlador de MercadoPago
            // para crear la preferencia con el token del vendedor.
            $preference = (new MercadoPagoController())->createPreference(
                $vendorAccessToken,
                $items,
                $payer,
                $order->id,
                $backUrls,
                $commission,
                $subtotal
            );

            $request->session()->forget(['compra', 'datosCompra']);

            // Redirigir al usuario al Checkout Pro
            return redirect()->away($preference->init_point);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ocurrió un error al procesar el pago. Por favor, inténtelo de nuevo más tarde.']);
        }
    }
}
