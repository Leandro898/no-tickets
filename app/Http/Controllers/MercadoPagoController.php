<?php

namespace App\Http\Controllers;

use App\Mail\TicketsPurchasedMail; // Importa tu clase Mailable
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
use App\Mail\PurchaseWelcomeMail;
use Illuminate\Support\Facades\URL;


class MercadoPagoController extends Controller
{
    public function __construct()
    {
        // Leemos directamente del config/mercadopago.php
        $token       = config('mercadopago.platform_access_token');
        $sandboxMode = config('mercadopago.sandbox', true);

        if (! $token) {
            Log::critical('MercadoPago: falta configurar platform_access_token en config/mercadopago.php (o en .env MERCADO_PAGO_ACCESS_TOKEN)');
            // opcional: abort(500, 'MercadoPago sin token');
        }

        // 1) Access Token (vendedor o plataforma)
        MercadoPagoConfig::setAccessToken($token);

        // 2) Entorno: LOCAL = sandbox, SERVER = producción
        MercadoPagoConfig::setRuntimeEnviroment(
            $sandboxMode
                ? MercadoPagoConfig::LOCAL
                : MercadoPagoConfig::SERVER
        );

        Log::info('MercadoPagoConfig inicializado', [
            'sandbox' => $sandboxMode,
        ]);
    }

    /**
     * Crea una preferencia de pago en Mercado Pago.
     *
     * @param  array   $items             Lista de items en el formato que espera Mercado Pago.
     * @param  array   $payer             Datos del comprador.
     * @param  string  $externalReference Tu ID de orden.
     * @param  array   $backUrls          ['success'=>…, 'failure'=>…, 'pending'=>…]
     * @return Preference
     *
     * @throws MPApiException
     * @throws \Exception
     */
    public function createPreference(array $items, array $payer, string $externalReference, array $backUrls): Preference
    {
        // Preparar la carga
        $requestData = [
            'items'               => $items,
            'payer'               => $payer,
            'back_urls'           => $backUrls,
            'auto_return'         => 'approved',
            'notification_url'    => config('mercadopago.notification_url'),
            'statement_descriptor' => 'ENTRADAS',
            'external_reference'  => $externalReference,
            'binary_mode'         => false,
        ];

        Log::info('Enviando preferencia a MercadoPago', ['request' => $requestData]);

        $client = new PreferenceClient();
        $preference = $client->create($requestData);

        return $preference;
    }

    /**
     * Maneja las notificaciones de webhook de Mercado Pago.
     * Esta es la URL a la que Mercado Pago enviará actualizaciones de pago.
     * La ruta asociada a este método será POST /api/mercadopago/webhook.
     */
    public function handleWebhook(Request $request)
    {
        Log::info('Webhook de Mercado Pago recibido.', $request->all());

        // Extraemos el ID del pago
        $paymentId = null;
        $topic = $request->input('topic');
        $type  = $request->input('type');

        if ($topic === 'payment' && $request->has('id')) {
            $paymentId = $request->input('id');
        } elseif (in_array($type, ['payment', 'created_payment', 'updated_payment']) && $request->input('data.id')) {
            $paymentId = $request->input('data.id');
        } elseif ($request->has('action') && Str::startsWith($request->input('action'), 'payment.') && $request->input('data.id')) {
            $paymentId = $request->input('data.id');
        }

        if (! $paymentId) {
            Log::warning('Webhook: no se encontró paymentId en el payload.');
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            $payment = (new PaymentClient())->get($paymentId);

            DB::transaction(function () use ($payment) {
                $order = Order::where('id', $payment->external_reference)
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    Log::warning("Webhook: Orden no encontrada (external_reference={$payment->external_reference}).");
                    return;
                }

                // 1) Actualizo estado interno
                $newStatus = $this->mapMercadoPagoStatusToOrderStatus($payment->status);
                if ($order->payment_status !== $newStatus) {
                    $order->payment_status = $newStatus;
                    $order->mp_payment_id  = $payment->id;
                    $order->save();
                    Log::info("Orden {$order->id} actualizada a '{$newStatus}' via webhook.");
                }

                // 2) Si se aprobó y aún no hay tickets, los genero
                if ($newStatus === 'approved' && ! $order->purchasedTickets()->exists()) {
                    $itemsData = json_decode($order->items_data, true);
                    foreach ($itemsData as $item) {
                        $entrada = \App\Models\Entrada::find($item['entrada_id']);
                        for ($i = 0; $i < $item['cantidad']; $i++) {
                            // Generar código único y QR
                            $uuid   = (string) \Illuminate\Support\Str::uuid();
                            $qrPath = "qrcodes/{$uuid}.png";

                            if (! Storage::disk('public')->exists('qrcodes')) {
                                Storage::disk('public')->makeDirectory('qrcodes');
                            }
                            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                ->size(300)->margin(4)
                                ->generate($uuid, storage_path("app/public/{$qrPath}"));

                            // Crear PurchasedTicket
                            \App\Models\PurchasedTicket::create([
                                'order_id'     => $order->id,
                                'entrada_id'   => $entrada->id,
                                'unique_code'  => $uuid,
                                'qr_path'      => $qrPath,
                                'status'       => 'valid',
                                'buyer_name'   => $order->buyer_full_name,
                                'ticket_type'  => $entrada->nombre,
                            ]);
                        }
                        // Reducir stock
                        $entrada->decrement('stock_actual', $item['cantidad']);
                    }

                    Log::info("PurchasedTicket generados para la orden {$order->id}");
                }
            });

            return response()->json(['status' => 'success'], 200);
        } catch (MPApiException $e) {
            Log::error("MPApiException en webhook: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => 'MP API error'], 500);
        } catch (\Exception $e) {
            Log::error("Error interno en webhook: {$e->getMessage()}");
            return response()->json(['status' => 'error', 'message' => 'Internal error'], 500);
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

    public function success(Request $request, Order $order)
    {
        Log::info("MercadoPago Success redirección para orden {$order->id}", $request->all());
        $order->refresh();

        if ($order->payment_status !== 'approved') {
            return redirect()
                ->route('purchase.pending', ['order' => $order->id])
                ->with('info', 'Estamos procesando tu pago. Te avisaremos cuando esté confirmado.');
        }

        // ¿Existe ya ese e-mail?
        $isNew = ! User::where('email', $order->buyer_email)->exists();

        // Crear o recuperar usuario
        $user = User::firstOrCreate(
            ['email' => $order->buyer_email],
            [
                'name'     => $order->buyer_full_name,
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // Solo enviamos correo la primera vez que procesamos este pedido
        if (is_null($order->email_sent_at)) {
            if ($isNew) {
                // 1) Primera compra: envío de bienvenida + reset + tickets

                // Generar token de reset y URL
                $token = Password::broker()->createToken($user);
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                Mail::to($user->email)
                    ->send(new PurchaseWelcomeMail($order, $resetUrl));

                Log::info("Bienvenida + tickets enviada a {$user->email}");
            } else {
                // 2) Compra adicional: sólo tickets
                Mail::to($user->email)
                    ->send(new TicketsPurchasedMail($order));

                Log::info("TicketsPurchasedMail enviada a {$user->email}");
            }

            $order->update(['email_sent_at' => now()]);
        }

        // Asignar rol, loguear y redirigir
        if (! $user->hasRole('cliente')) {
            $user->assignRole('cliente');
        }
        Auth::login($user);

        return redirect()
            ->route('mis-entradas')
            ->with('success', '¡Compra exitosa! Tus entradas ya están disponibles.');
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

