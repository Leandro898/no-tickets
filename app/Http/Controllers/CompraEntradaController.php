<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Entrada;
use App\Models\Order;
use App\Models\PurchasedTicket;
use Illuminate\Support\Str;
// --- INICIO: Dependencias de Mercado Pago (asegúrate de que estén instaladas vía Composer) ---
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
// --- FIN: Dependencias de Mercado Pago ---
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Asegúrate de que este paquete esté instalado
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CompraEntradaController extends Controller
{
    // El constructor ya no necesita establecer el access token aquí,
    // ya que lo haremos dinámicamente por cada compra con el token del productor.
    // public function __construct()
    // {
    //     // Ya no inicializamos el SDK aquí con un token global
    // }

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

            // --- Obtener el Access Token del productor del evento ---
            // Asegúrate de que tu modelo Organizador tenga un campo mp_access_token
            // y que los organizadores tengan este token guardado.
            $producerAccessToken = $evento->organizador->mp_access_token ?? null;

            if (is_null($producerAccessToken)) {
                Log::error('CompraEntradaController: No se encontró el Access Token de Mercado Pago para el organizador del evento ' . $evento->id);
                throw new \Exception('El organizador de este evento no tiene una cuenta de Mercado Pago conectada. Intente de nuevo más tarde.');
            }

            // --- Establecer el Access Token del productor para esta operación de Mercado Pago ---
            MercadoPagoConfig::setAccessToken($producerAccessToken);

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
                        "quantity" => (int) $cantidad, // <<-- ¡CORRECCIÓN APLICADA AQUÍ!
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

            // --- Construir el array de la preferencia para el cliente ---
            // Asegúrate de que config('mercadopago.notification_url') esté definido y sea una URL válida
            $preferenceData = [
                "items" => $itemsForMercadoPago,
                "notification_url" => config('mercadopago.notification_url'),
                "back_urls" => [
                    "success" => route('purchase.success', ['order' => $order->id]),
                    "failure" => route('purchase.failure', ['order' => $order->id]),
                    "pending" => route('purchase.pending', ['order' => $order->id]),
                ],
                "auto_return" => "approved",
                "external_reference" => (string) $order->id, // Asegurarse de que sea string
                "payer" => [
                    "email" => $validatedData['email'],
                    "name" => $validatedData['nombre'],
                    "phone" => [
                        "number" => $validatedData['buyer_phone'],
                    ],
                ]
            ];

            // Instanciar el cliente de preferencias y crear la preferencia
            $preferenceClient = new PreferenceClient();
            $preference = $preferenceClient->create($preferenceData);

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

            Log::error('Error de API al iniciar compra con Mercado Pago: Status ' . $apiStatusCode . ' - Content: ' . json_encode($apiResponseContent));

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
            Log::error('Error general al iniciar compra con Mercado Pago: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->withErrors(['error' => 'Hubo un error al procesar tu compra. Por favor, inténtelo de nuevo.'])->withInput();
        } finally {
            // Es buena práctica resetear el Access Token si se estableció dinámicamente
            // para evitar que afecte otras operaciones que usen el token de la plataforma.
            // Si tu SDK::setAccessToken está en el constructor de AppServiceProvider o un middleware,
            // entonces aquí podrías volver a establecerlo a tu token de plataforma.
            // MercadoPagoConfig::setAccessToken(config('mercadopago.platform_access_token'));
        }
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook de Mercado Pago recibido en CompraEntradaController.', $request->all());

        if ($request->input('topic') === 'payment') {
            $paymentId = $request->input('id');

            try {
                // Para consultar el pago, necesitamos el access_token del productor que recibió el pago.
                // Esto es complejo si no sabemos de antemano qué productor es.
                // Una solución es guardar el mp_user_id del productor en la orden (Order model).
                // Por ahora, para la prueba, usaremos el token de la plataforma si no lo tenemos del productor.
                // En un escenario real, deberías cargar la orden por external_reference,
                // luego obtener el evento, luego el organizador, y usar su mp_access_token.

                $platformAccessToken = config('mercadopago.platform_access_token');
                if (is_null($platformAccessToken)) {
                    Log::critical('Webhook: Mercado Pago Platform Access Token es NULL. No se puede consultar el pago.');
                    return response()->json(['status' => 'error', 'message' => 'Configuración de token de plataforma faltante.'], 500);
                }
                MercadoPagoConfig::setAccessToken($platformAccessToken); // Usamos el token de la plataforma para consultar el pago

                $paymentClient = new PaymentClient();
                $payment = $paymentClient->get($paymentId);

                // <<-- CORRECCIÓN ADICIONAL AQUÍ EN EL WEBHOOK -->>
                // El status del objeto $payment es el estado del pago ('approved', 'pending', 'rejected'), no un código HTTP.
                if ($payment->status === 'approved') {
                    $externalReference = $payment->external_reference;

                    $order = Order::find($externalReference);

                    if ($order && $order->payment_status !== 'approved') {
                        DB::beginTransaction();
                        try {
                            $order->payment_status = $payment->status; // 'approved', 'pending', 'rejected', etc.
                            $order->mp_payment_id = $payment->id;
                            $order->save();

                            if ($order->payment_status === 'approved') {
                                $itemsData = json_decode($order->items_data, true);

                                if (is_array($itemsData)) {
                                    foreach ($itemsData as $item) {
                                        $entrada = Entrada::find($item['entrada_id']);
                                        $cantidad = $item['cantidad'];

                                        if ($entrada) {
                                            for ($i = 0; $i < $cantidad; $i++) {
                                                $uniqueCode = (string) Str::uuid();
                                                $qrPath = 'qrcodes/' . $uniqueCode . '.png';
                                                $qrContent = route('ticket.validate', ['code' => $uniqueCode]);

                                                // Asegúrate de que el directorio storage/app/public/qrcodes exista y sea escribible
                                                // php artisan storage:link si no lo has hecho
                                                QrCode::format('png')->size(300)->generate($qrContent, storage_path('app/public/' . $qrPath));

                                                PurchasedTicket::create([
                                                    'order_id' => $order->id,
                                                    'entrada_id' => $entrada->id,
                                                    'unique_code' => $uniqueCode,
                                                    'qr_path' => $qrPath,
                                                    'status' => 'valid',
                                                ]);
                                            }

                                            $entrada->decrement('stock_actual', $cantidad);
                                        } else {
                                            Log::warning('Webhook: Entrada ID ' . $item['entrada_id'] . ' no encontrada para la orden ' . $order->id);
                                        }
                                    }
                                } else {
                                    Log::error('Webhook: items_data no es un array válido para la orden ' . $order->id);
                                }
                            }

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error('Error procesando webhook de Mercado Pago para orden ' . $order->id . ': ' . $e->getMessage(), ['exception' => $e]);
                        }
                    } elseif ($order && $order->payment_status === 'approved') {
                        Log::info('Webhook: Orden ' . $order->id . ' ya estaba aprobada. Ignorando.', $request->all());
                    } else {
                        Log::warning('Webhook: Orden no encontrada o ya procesada con external_reference ' . $externalReference);
                    }
                } else {
                    // Si el estado del pago no es 'approved', loguea el estado real para depuración.
                    Log::warning('Webhook: Pago ' . $paymentId . ' no está en estado "approved". Estado actual: ' . $payment->status);
                }
            } catch (MPApiException $e) {
                Log::error('Webhook (CompraEntradaController) MP API Error: ' . $e->getApiResponse()->getContent());
                return response()->json(['status' => 'error', 'message' => 'Error de API al consultar el pago.'], 500);
            } catch (\Exception $e) {
                Log::error('Error al procesar el pago de Mercado Pago con ID ' . $paymentId . ': ' . $e->getMessage(), ['exception' => $e]);
            }
        }

        return response()->json(['status' => 'success'], 200);
    }

    public function success(Request $request, Order $order)
    {
        return view('purchase.success', compact('order'));
    }

    public function failure(Request $request, Order $order)
    {
        return view('purchase.failure', compact('order'));
    }

    public function pending(Request $request, Order $order)
    {
        return view('purchase.pending', compact('order'));
    }

    public function index()
    {
        $tickets = PurchasedTicket::with('entrada.evento')->latest()->paginate(10);
        return view('tickets.index', compact('tickets'));
    }
}