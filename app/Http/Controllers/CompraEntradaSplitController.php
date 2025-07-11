<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Order;
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
        //dd('store() llamado', $request->all());

        $data = $request->validate([
            'entrada_id' => 'required|integer|exists:entradas,id',
            'cantidad'   => 'required|integer|min:1',
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
            'compra.cantidad'   => $data['cantidad'],
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
        $cantidad  = session('compra.cantidad', 1);

        if (! $entradaId) {
            return redirect()
                ->route('eventos.comprar.split', $evento)
                ->withErrors('Debes seleccionar una entrada primero.');
        }

        $entrada  = $evento->entradas->firstWhere('id', $entradaId);
        $subtotal = $entrada->precio * $cantidad;

        return view('comprar-entrada-datos', compact('evento', 'entrada', 'cantidad', 'subtotal'));
    }

    /**
     * Paso 2 (POST): recibo los datos personales, creo la orden
     * y genero la preferencia de Mercado Pago.
     */

    public function storeDatos(Request $request, Evento $evento)
    {
        $validated = $request->validate([
            'nombre'    => 'required|string|max:255',
            'email'     => 'required|email',
            'buyer_dni' => 'nullable|string|max:20',
            'whatsapp'  => 'nullable|string|max:30',
        ]);

        // Recupero selección previa
        $entradaId = session('compra.entrada_id');
        $cantidad  = session('compra.cantidad', 1);

        // Calculo subtotal
        $entrada  = $evento->entradas()->findOrFail($entradaId);
        $subtotal = $entrada->precio * $cantidad;

        DB::beginTransaction();

        try {
            // 1) Creo la orden en BD
            $order = Order::create([
                'event_id'         => $evento->id,
                'buyer_full_name'  => $validated['nombre'],
                'buyer_email'      => $validated['email'],
                'buyer_dni'        => $validated['buyer_dni'],
                'buyer_phone'      => $validated['whatsapp'],
                'total_amount'     => $subtotal,
                'payment_status'   => 'pending',
                'items_data'       => json_encode([[
                    'entrada_id'      => $entrada->id,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $entrada->precio,
                ]]),
            ]);

            // 2) Seteo el access token del organizador
            $vendedor = $evento->organizador;
            if (! $vendedor || ! $vendedor->mp_access_token) {
                throw new Exception("El organizador no tiene Mercado Pago conectado.");
            }
            MercadoPagoConfig::setAccessToken($vendedor->mp_access_token);

            // 3) Creamos la preferencia usando tu helper
            /** @var MercadoPagoController $mp */
            $mp = app(MercadoPagoController::class);

            $items = [[
                'title'      => $entrada->nombre,
                'quantity'   => (int)$cantidad,
                'unit_price' => (float)$entrada->precio,
            ]];

            $payer = [
                'email'          => $validated['email'],
                'name'           => $validated['nombre'],
                'identification' => [
                    'type'   => 'DNI',
                    'number' => $validated['buyer_dni'] ?? '',
                ],
            ];

            $backUrls = [
                'success' => route('purchase.success', ['order' => $order->id]),
                'failure' => route('purchase.failure', ['order' => $order->id]),
                'pending' => route('purchase.pending', ['order' => $order->id]),
            ];

            $preference = $mp->createPreference($items, $payer, (string)$order->id, $backUrls);

            // 4) Convierte a array para log
            $prefArray = json_decode(json_encode($preference), true);
            Log::info('MP Preference creada:', $prefArray);

            DB::commit();

            // 5) Redirijo al usuario al init_point o sandbox_init_point
            $url = $prefArray['init_point'] ?? $prefArray['sandbox_init_point'] ?? null;
            if (! $url) {
                Log::error('MP Preference sin URL de redirección:', $prefArray);
                return back()->withErrors('No se pudo generar la URL de pago.');
            }

            return redirect($url);
        } catch (MPApiException $mpEx) {
            DB::rollBack();
            Log::error('MPApiException: ' . $mpEx->getMessage());
            Log::error('MPApiException response: ' . json_encode($mpEx->getApiResponse(), JSON_PRETTY_PRINT));
            return back()->withErrors('Error de Mercado Pago. Revisa los logs para más detalles.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error inesperado en storeDatos: " . $e->getMessage());
            return back()->withErrors('No se pudo procesar la compra. Intenta nuevamente.');
        }
    }
}
