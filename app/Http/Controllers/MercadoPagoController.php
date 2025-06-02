<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use MercadoPago\Resources\Preference;
use App\Models\Entrada;
use App\Models\Order;
use App\Models\PurchasedTicket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class MercadoPagoController extends Controller
{
    public function __construct()
    {
        $platformAccessToken = config('mercadopago.platform_access_token');

        if (is_null($platformAccessToken)) {
            Log::critical('Mercado Pago Access Token de Plataforma es NULL. La integración de Checkout Pro no funcionará correctamente.');
        } else {
            MercadoPagoConfig::setAccessToken($platformAccessToken);
            // ¡CORRECCIÓN FINAL AQUÍ! Volvemos a usar MercadoPagoConfig::LOCAL y MercadoPagoConfig::SERVER
            // Estas constantes son las que tu versión del SDK reconoce.
            MercadoPagoConfig::setRuntimeEnviroment(config('mercadopago.sandbox') ? MercadoPagoConfig::LOCAL : MercadoPagoConfig::SERVER);
            Log::info('MercadoPagoConfig: Access Token de plataforma configurado en el constructor.');
        }
    }

    /**
     * Crea una preferencia de pago en Mercado Pago para una orden dada.
     * Este método será llamado *desde* el CompraEntradaController.
     * No es una ruta web directa para el usuario.
     *
     * @param array $items Array de ítems formateado para Mercado Pago.
     * @param array $payer Array de datos del pagador formateado para Mercado Pago.
     * @param string $externalReference Tu ID de orden/referencia externa.
     * @param array $backUrls URLs de retorno (success, failure, pending).
     * @return Preference El objeto Preference de Mercado Pago.
     * @throws MPApiException
     * @throws \Exception
     */
    public function createPreference(array $items, array $payer, string $externalReference, array $backUrls): Preference
    {
        // Asegúrate de que el notification_url está bien configurado en config/mercadopago.php
        $notificationUrl = config('mercadopago.notification_url');
        if (empty($notificationUrl)) {
            Log::critical('Mercado Pago Notification URL es NULL o vacío. El webhook no funcionará correctamente.');
            throw new \Exception('La URL de notificación de Mercado Pago no está configurada.');
        }

        $requestData = [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => [
                "excluded_payment_methods" => [],
                "excluded_payment_types" => [],
                "installments" => 12, // Permite hasta 12 cuotas
                "default_payment_method_id" => null
            ],
            "back_urls" => $backUrls,
            "statement_descriptor" => "TU_NEGOCIO", // Reemplaza con el nombre de tu negocio en el extracto de tarjeta
            "external_reference" => $externalReference,
            "expires" => true,
            "expiration_date_from" => Carbon::now()->format('Y-m-d\TH:i:s.000O'),
            "expiration_date_to" => Carbon::now()->copy()->addHours(2)->format('Y-m-d\TH:i:s.000O'), // Preferencia válida por 2 horas
            "auto_return" => 'approved', // Redirecciona automáticamente solo si el pago es aprobado
            "notification_url" => $notificationUrl,
            "binary_mode" => false // Permite que Mercado Pago realice reintentos automáticos
            // Para Checkout Pro simple, NO incluimos 'marketplace_settings'.
        ];

        Log::info('Datos de preferencia enviados a Mercado Pago:', ['request' => $requestData]);

        $client = new PreferenceClient();
        return $client->create($requestData);
    }

    /**
     * Maneja las notificaciones de webhook de Mercado Pago.
     * Esta es la URL a la que Mercado Pago enviará actualizaciones de pago.
     * La ruta asociada a este método será POST /api/mercadopago/webhook.
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Webhook de Mercado Pago recibido en MercadoPagoController.', $request->all());

        $topic = $request->input('topic');
        $type = $request->input('type');

        $paymentId = null;

        if ($topic === 'payment' && $request->has('id')) {
            $paymentId = $request->input('id');
            Log::info("Webhook de Mercado Pago: Notificación (topic) con ID: {$paymentId}.");
        } elseif (($type === 'payment' || $type === 'created_payment' || $type === 'updated_payment') && $request->has('data.id')) {
            $paymentId = $request->input('data.id');
            Log::info("Webhook de Mercado Pago: Notificación (type/data.id) con ID: {$paymentId}.");
        } elseif ($request->has('action') && Str::startsWith($request->input('action'), 'payment.') && $request->has('data.id')) {
            $paymentId = $request->input('data.id');
            Log::info("Webhook de Mercado Pago: Notificación (action) con ID: {$paymentId}.");
        } else {
            Log::info('Webhook de Mercado Pago: Tipo de notificación no relevante para pago o ID no encontrado.', $request->all());
            return response()->json(['status' => 'ignored', 'message' => 'Tipo de notificación no relevante o ID de pago no encontrado.'], 200);
        }

        if (is_null($paymentId)) {
            Log::warning('Webhook de Mercado Pago: ID de pago no pudo ser extraído de la notificación.', $request->all());
            return response()->json(['status' => 'error', 'message' => 'ID de pago no pudo ser extraído.'], 400);
        }

        try {
            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);

            Log::info('Detalles del pago de Mercado Pago recuperados.', ['payment_id' => $payment->id, 'status' => $payment->status]);

            $externalReference = $payment->external_reference;
            if (empty($externalReference)) {
                Log::error('Webhook: external_reference es nulo o vacío para el pago ' . $payment->id);
                return response()->json(['status' => 'error', 'message' => 'external_reference no encontrado en el pago de MP'], 400);
            }

            $order = Order::find($externalReference);

            if ($order) {
                $currentOrderStatus = $this->mapMercadoPagoStatusToOrderStatus($payment->status);

                if ($order->payment_status !== $currentOrderStatus) {
                    DB::beginTransaction();
                    try {
                        $order->payment_status = $currentOrderStatus;
                        $order->mp_payment_id = $payment->id;
                        // Asegúrate de que tu migración tenga este campo 'payment_details' (tipo JSON)
                        //$order->payment_details = json_encode($payment);
                        $order->save();

                        // Lógica específica si el pago es aprobado (emitir tickets, decrementar stock)
                        if ($order->payment_status === 'approved') {
                            // Asegúrate de que los tickets no se dupliquen si el webhook se llama varias veces.
                            if (!$order->purchasedTickets()->exists()) {
                                // Asegúrate de que tu migración tenga este campo 'items_data' (tipo JSON)
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

                                                if (!Storage::disk('public')->exists('qrcodes')) {
                                                    Storage::disk('public')->makeDirectory('qrcodes');
                                                }
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
                                            Log::info("Stock de entrada '{$entrada->nombre}' decrementado en {$cantidad}. Nuevo stock: {$entrada->stock_actual}");
                                        } else {
                                            Log::warning('Webhook: Entrada ID ' . $item['entrada_id'] . ' no encontrada para la orden ' . $order->id);
                                        }
                                    }
                                    Log::info('Tickets generados y stock actualizado para la orden ' . $order->id);
                                } else {
                                    Log::error('Webhook: items_data no es un array válido en la orden ' . $order->id);
                                }
                            } else {
                                Log::info('Webhook: Tickets ya emitidos para la orden ' . $order->id . '. No se requiere re-emisión.');
                            }
                        }

                        DB::commit();
                        Log::info('Orden ' . $order->id . ' actualizada a estado "' . $currentOrderStatus . '" por webhook.');
                    } catch (\Exception $e) {
                        DB::rollBack();
                        Log::error('Error en transacción de webhook para orden ' . $order->id . ': ' . $e->getMessage(), ['exception' => $e]);
                        return response()->json(['status' => 'error', 'message' => 'Error al actualizar la orden en transacción.'], 500);
                    }
                } else {
                    Log::info('Webhook: Estado de pago para orden ' . $order->id . ' no ha cambiado. Ignorando.', ['current_status' => $order->payment_status, 'new_status' => $currentOrderStatus]);
                }
            } else {
                Log::warning('Webhook: Orden no encontrada con external_reference ' . $externalReference . ' para el pago ' . $paymentId);
                return response()->json(['status' => 'not_found', 'message' => 'Orden no encontrada en el sistema.'], 404);
            }
        } catch (MPApiException $e) {
            $apiErrorContent = $e->getApiResponse() ? $e->getApiResponse()->getContent() : 'No API response content';
            if (is_array($apiErrorContent) || is_object($apiErrorContent)) {
                $apiErrorContent = json_encode($apiErrorContent);
            } else {
                $apiErrorContent = (string) $apiErrorContent;
            }
            $statusCode = $e->getApiResponse() ? $e->getApiResponse()->getStatusCode() : 500;
            Log::error('Webhook MP API Error al consultar pago: ' . $apiErrorContent, ['exception' => $e, 'status_code' => $statusCode]);
            return response()->json(['status' => 'error', 'message' => 'Error de API al consultar el pago.'], $statusCode);
        } catch (\Exception $e) {
            Log::error('Error general al procesar el webhook de Mercado Pago con ID ' . ($paymentId ?? 'N/A') . ': ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Error interno del servidor al procesar webhook.'], 500);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Mapea el estado de pago de Mercado Pago a un estado de orden interno.
     * Puedes personalizar estos estados según tu modelo de Order.
     */
    private function mapMercadoPagoStatusToOrderStatus($mpStatus): string
    {
        switch ($mpStatus) {
            case 'approved':
                return 'approved';
            case 'pending':
            case 'in_process':
            case 'in_mediation':
                return 'pending';
            case 'rejected':
            case 'cancelled':
            case 'refunded':
            case 'charged_back':
                return 'cancelled';
            default:
                return 'unknown';
        }
    }

    // --- Métodos de retorno de Mercado Pago (para la redirección del usuario) ---
    public function success(Request $request, Order $order)
    {
        Log::info('MercadoPago Success redirección para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.success', compact('order'));
    }

    public function failure(Request $request, Order $order)
    {
        Log::warning('MercadoPago Failed redirección para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.failure', compact('order'));
    }

    public function pending(Request $request, Order $order)
    {
        Log::info('MercadoPago Pending redirección para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.pending', compact('order'));
    }

    // --- Métodos para OAuth de Mercado Pago (para el futuro Marketplace) ---
    public function connect()
    {
        $clientId = config('mercadopago.client_id');
        $redirectUri = route('mercadopago.callback');

        $authUrl = "https://auth.mercadopago.com.ar/oauth/authorize?" .
            "client_id={$clientId}&" .
            "response_type=code&" .
            "platform_id=mp&" .
            "redirect_uri=" . urlencode($redirectUri);

        Log::info('Iniciando conexión OAuth con Mercado Pago', ['url' => $authUrl]);
        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            Log::error('OAuth Callback: No se recibió el código de autorización de Mercado Pago.');
            return redirect()->route('mercadopago.status')->with('error', 'No se pudo conectar la cuenta de Mercado Pago. Código de autorización no recibido.');
        }

        try {
            $client = new Client();
            $response = $client->post('https://api.mercadopago.com/oauth/token', [
                'json' => [
                    'client_id' => config('mercadopago.client_id'),
                    'client_secret' => config('mercadopago.client_secret'),
                    'code' => $code,
                    'redirect_uri' => route('mercadopago.callback'),
                    'grant_type' => 'authorization_code',
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $user = Auth::user();
            if ($user) {
                $user->mp_access_token = $data['access_token'];
                $user->mp_refresh_token = $data['refresh_token'];
                $user->mp_public_key = $data['public_key'] ?? null;
                $user->mp_user_id = $data['user_id'] ?? null;
                $user->mp_expires_in = Carbon::now()->addSeconds($data['expires_in']);
                $user->save();

                Log::info('Tokens de Mercado Pago guardados para el usuario ' . $user->id, ['mp_user_id' => $data['user_id'] ?? 'N/A']);
                return redirect()->route('mercadopago.status')->with('success', 'Cuenta de Mercado Pago conectada exitosamente!');
            } else {
                Log::error('OAuth Callback: Usuario no autenticado o no encontrado para guardar tokens de Mercado Pago.');
                return redirect()->route('mercadopago.status')->with('error', 'Error interno al guardar los tokens de Mercado Pago.');
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $errorContent = json_decode($response->getBody()->getContents(), true);
            Log::error('Error de API al intercambiar código OAuth: ' . $e->getMessage(), ['error_response' => $errorContent]);
            return redirect()->route('mercadopago.status')->with('error', 'Error al conectar con Mercado Pago: ' . ($errorContent['message'] ?? 'Error desconocido.'));
        } catch (\Exception $e) {
            Log::error('Error inesperado en OAuth Callback: ' . $e->getMessage());
            return redirect()->route('mercadopago.status')->with('error', 'Ocurrió un error inesperado al conectar tu cuenta de Mercado Pago.');
        }
    }

    public function unlinkMPAccount(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->mp_access_token = null;
            $user->mp_refresh_token = null;
            $user->mp_public_key = null;
            $user->mp_user_id = null;
            $user->mp_expires_in = null;
            $user->save();

            Log::info('Cuenta de Mercado Pago desvinculada para el usuario ' . $user->id);
            return redirect()->route('mercadopago.status')->with('success', 'Cuenta de Mercado Pago desvinculada.');
        }
        return redirect()->route('mercadopago.status')->with('error', 'No se pudo desvincular la cuenta de Mercado Pago.');
    }
}