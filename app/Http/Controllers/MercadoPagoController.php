<?php

namespace App\Http\Controllers;

use App\Mail\PurchasedTicketsMail; // Importa tu clase Mailable
use Illuminate\Support\Facades\Mail; // Importa la Facade Mail
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
use Illuminate\Support\Facades\Password;
use App\Mail\CuentaCreadaMail;

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
     * Este método ser llamado *desde* el CompraEntradaController.
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
            "statement_descriptor" => "Innova Ticket", // Reemplaza con el nombre de tu negocio en el extracto de tarjeta
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
        } elseif (($type === 'payment' || $type === 'created_payment' || $type === 'updated_payment') && $request->has('data.id')) {
            $paymentId = $request->input('data.id');
        } elseif ($request->has('action') && \Illuminate\Support\Str::startsWith($request->input('action'), 'payment.') && $request->has('data.id')) {
            $paymentId = $request->input('data.id');
        } else {
            return response()->json(['status' => 'ignored'], 200);
        }

        if (!$paymentId) {
            return response()->json(['status' => 'error', 'message' => 'ID no encontrado.'], 400);
        }

        try {
            $paymentClient = new PaymentClient();
            $payment = $paymentClient->get($paymentId);

            DB::transaction(function () use ($payment) {
                $order = Order::where('id', $payment->external_reference)->lockForUpdate()->first();

                if (!$order) {
                    Log::warning('Webhook: Orden no encontrada con external_reference ' . $payment->external_reference);
                    return;
                }

                $currentStatus = $this->mapMercadoPagoStatusToOrderStatus($payment->status);

                if ($order->payment_status === $currentStatus) {
                    Log::info('Webhook: Estado de orden sin cambios para ID ' . $order->id);
                    return;
                }

                $order->payment_status = $currentStatus;
                $order->mp_payment_id = $payment->id;
                $order->save();

                if ($currentStatus === 'approved') {
                    if (!$order->purchasedTickets()->exists()) {
                        $itemsData = json_decode($order->items_data, true);
                        foreach ($itemsData as $item) {
                            $entrada = Entrada::find($item['entrada_id']);
                            $cantidad = $item['cantidad'];
                            for ($i = 0; $i < $cantidad; $i++) {
                                $uniqueCode = (string) Str::uuid();
                                $qrPath = 'qrcodes/' . $uniqueCode . '.png';
                                $qrContent = $uniqueCode;
                                if (!Storage::disk('public')->exists('qrcodes')) {
                                    Storage::disk('public')->makeDirectory('qrcodes');
                                }
                                QrCode::format('png')->size(300)->margin(4)->generate($qrContent, storage_path('app/public/' . $qrPath));
                                PurchasedTicket::create([
                                    'order_id' => $order->id,
                                    'entrada_id' => $entrada->id,
                                    'unique_code' => $uniqueCode,
                                    'qr_path' => $qrPath,
                                    'status' => 'valid',
                                    'buyer_name' => $order->buyer_full_name,
                                    'ticket_type' => $entrada->nombre, // Asegurate que el campo en la tabla entradas se llama 'nombre'
                                ]);
                            }
                            $entrada->decrement('stock_actual', $cantidad);
                        }
                    }

                    if (is_null($order->email_sent_at)) {
                        $tickets = $order->purchasedTickets;
                        try {
                            $user = User::where('email', $order->buyer_email)->first();

                            //cambiar de aca
                            try {
                                $user = \App\Models\User::firstOrCreate(
                                    ['email' => $order->buyer_email],
                                    [
                                        'name' => $order->buyer_full_name,
                                        'password' => bcrypt(Str::random(12))
                                    ]
                                );
                            
                                $token = Password::createToken($user);
                            
                                $resetUrl = url(route('password.reset', [
                                    'token' => $token,
                                    'email' => $user->email,
                                ]));
                            
                                Mail::to($user->email)->send(new CuentaCreadaMail(
                                    $user->name,
                                    $resetUrl,
                                    $tickets
                                ));
                            
                                info("Correo enviado con enlace de contraseña a {$user->email}");
                            
                            } catch (\Throwable $e) {
                                // Si algo falla, lo logueás
                                \Log::error('Error al crear usuario o enviar correo de cuenta creada: ' . $e->getMessage());
                            
                                // Como fallback, mandás el correo sin link
                                Mail::to($order->buyer_email)->send(new CuentaCreadaMail(
                                    $order->buyer_full_name,
                                    null,
                                    $tickets
                                ));
                            }

                            // hasta aca

                            $order->email_sent_at = now();
                            $order->save();
                            Log::info('Correo enviado a ' . $order->buyer_email);

                            // Enviar mensaje por WhatsApp
                            if ($order->buyer_phone && $order->purchasedTickets()->exists()) {
                                $firstTicket = $order->purchasedTickets()->first();
                                $linkQR = route('qr.download', ['filename' => basename($firstTicket->qr_path)]);
                                $mensaje = "🎟️ ¡Gracias por tu compra!\nDescargá tu entrada aquí:\n$linkQR";

                                $numero = preg_replace('/\D/', '', $order->buyer_phone);
                                $urlWhatsApp = "https://api.whatsapp.com/send?phone=$numero&text=" . urlencode($mensaje);

                                // Log opcional para saber que se intentó enviar
                                Log::info('Link para enviar por WhatsApp: ' . $urlWhatsApp);
                            }
                        } catch (\Exception $e) {
                            Log::error('Error al enviar el email: ' . $e->getMessage());
                        }
                    } else {
                        Log::info('El correo ya fue enviado previamente para la orden ' . $order->id);
                    }
                }

                Log::info('Orden ' . $order->id . ' actualizada a "' . $currentStatus . '" por webhook.');
            });

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Error en webhook: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Error interno'], 500);
        }
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

    // --- Mtodos de retorno de Mercado Pago (para la redirección del usuario) ---
    public function success(Request $request, Order $order)
    {
        Log::info('MercadoPago Success redirección para orden ' . $order->id, $request->all());

        $order->refresh(); // Asegurarse de que el estado está actualizado

        if ($order->payment_status === 'approved') {
            // Crear o buscar el usuario por email
            $user = User::firstOrCreate(
                ['email' => $order->buyer_email],
                [
                    'name' => $order->buyer_full_name,
                    'password' => bcrypt(Str::random(12)) // contraseña temporal aleatoria
                ]
            );

            // if ($user->wasRecentlyCreated) {
            //     $token = Password::createToken($user);
            //     $resetLink = url("/reset-password/{$token}?email={$user->email}");
            
            //     $tickets = $order->purchasedTickets; // agregá esta línea
            
            //     Mail::to($user->email)->send(new \App\Mail\CuentaCreadaMail(
            //         $user->name,
            //         $resetLink,
            //         $tickets // pasá los tickets también acá
            //     ));
            // }
                 
            

            // Asignar rol "cliente" si no lo tiene
            if (!$user->hasRole('cliente')) {
                $user->assignRole('cliente');
            }

            // Loguear al usuario si aún no está logueado
            if (!Auth::check()) {
                Auth::login($user);
            }

            // Redirigir a "Mis Entradas"
            return redirect()->route('mis-entradas')
                ->with('success', '¡Compra exitosa! Tus entradas están disponibles.');
        }

        // Si el pago no está aprobado aún
        return redirect()->route('purchase.pending', ['order' => $order->id])
            ->with('info', 'Estamos procesando tu pago. Te avisaremos cuando esté confirmado.');
    }



    public function failure(Request $request, Order $order)
    {
        Log::warning('MercadoPago Failed redireccin para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.failure', compact('order'));
    }

    public function pending(Request $request, Order $order)
    {
        Log::info('MercadoPago Pending redireccin para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.pending', compact('order'));
    }
}

