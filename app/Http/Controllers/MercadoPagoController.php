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
use App\Models\MagicLink;
use App\Notifications\MagicLinkLogin;


class MercadoPagoController extends Controller
{
    public function __construct()
    {
        // Leemos directamente del config/mercadopago.php
        $token       = config('mercadopago.platform_access_token');
        $sandboxMode = config('mercadopago.sandbox', true);

        if (! $token) {
            //Log::critical('MercadoPago: falta configurar platform_access_token en config/mercadopago.php (o en .env MERCADO_PAGO_ACCESS_TOKEN)');
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

        // Log::info('MercadoPagoConfig inicializado', [
        //     'sandbox' => $sandboxMode,
        // ]);
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
        // Extraemos el ID del pago
        $paymentId = null;
        $topic     = $request->input('topic');
        $type      = $request->input('type');

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
                $order = Order::lockForUpdate()
                    ->where('id', $payment->external_reference)
                    ->first();

                if (! $order) {
                    Log::warning("Webhook: Orden no encontrada (external_reference={$payment->external_reference}).");
                    return;
                }

                // Actualizo estado interno si ha cambiado
                $newStatus = $this->mapMercadoPagoStatusToOrderStatus($payment->status);
                if ($order->payment_status !== $newStatus) {
                    $order->update([
                        'payment_status' => $newStatus,
                        'mp_payment_id'  => $payment->id,
                    ]);
                    Log::info("Orden {$order->id} actualizada a '{$newStatus}'.");
                }

                // Si se aprobó y aún no hay tickets, generarlos
                if ($newStatus === 'approved' && ! $order->purchasedTickets()->exists()) {
                    $itemsData = json_decode($order->items_data, true);

                    foreach ($itemsData as $item) {
                        $entrada = Entrada::find($item['entrada_id']);

                        for ($i = 0; $i < $item['cantidad']; $i++) {
                            // Generar código único y QR
                            $uuid   = (string) Str::uuid();
                            $qrPath = "qrcodes/{$uuid}.png";

                            Storage::disk('public')->put(
                                $qrPath,
                                QrCode::format('png')->size(300)->margin(4)->generate($uuid)
                            );

                            // Crear el registro en BD
                            $ticket = PurchasedTicket::create([
                                'order_id'     => $order->id,
                                'entrada_id'   => $entrada->id,
                                'unique_code'  => $uuid,
                                'short_code'   => $uuid,
                                'qr_path'      => $qrPath,
                                'status'       => 'valid',
                                'buyer_name'   => $order->buyer_full_name,
                                'ticket_type'  => $entrada->nombre,
                            ]);

                            Log::info("PurchasedTicket {$ticket->id} creado (QR en {$qrPath}).");

                            // Generar y guardar el PDF
                            $pdf = \PDF::loadView('tickets.pdf', [
                                'ticket' => $ticket,
                                'order'  => $order,
                            ]);

                            $filename = "entrada-{$ticket->short_code}.pdf";
                            $fullPath = storage_path("app/private/tickets/{$filename}");

                            if (! file_exists(dirname($fullPath))) {
                                mkdir(dirname($fullPath), 0755, true);
                            }

                            $pdf->save($fullPath);
                            $ticket->update(['pdf_path' => "tickets/{$filename}"]);

                            Log::info("PDF generado para ticket {$ticket->id}: {$fullPath}");
                        }

                        // Reducir stock
                        $entrada->decrement('stock_actual', $item['cantidad']);
                    }
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
        // 1️⃣ Refrescar la orden en BD y verificar estado
        $order->refresh();
        if ($order->payment_status !== 'approved') {
            return redirect()
                ->route('purchase.pending', ['order' => $order->id])
                ->with('info', 'Estamos procesando tu pago. Te avisaremos cuando esté confirmado.');
        }

        // 2️⃣ Comprobar si es la primera vez que usamos este email
        $isNew = ! User::where('email', $order->buyer_email)->exists();

        // 3️⃣ Crear o recuperar el usuario, con password aleatoria
        //    para cumplir la restricción NOT NULL en la tabla users
        $user = User::firstOrCreate(
            ['email' => $order->buyer_email],
            [
                'name'     => $order->buyer_full_name,
                'password' => bcrypt(Str::random(16)),
            ]
        );

        // 4️⃣ Envío de correo solo la primera vez que procesamos esta orden
        if (is_null($order->email_sent_at)) {
            if ($isNew) {
                //
                // —— PRIMERA COMPRA —— envío de bienvenida + enlace para configurar contraseña + tickets
                //

                // 4.1) Generar token y URL de reset de contraseña
                $token    = Password::broker()->createToken($user);
                $resetUrl = url(route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ], false));

                // 4.2) Enviar el Mailable con los adjuntos PDF y el reset link
                Mail::to($user->email)
                    ->send(new PurchaseWelcomeMail($order, $resetUrl));

                Log::info("PurchaseWelcomeMail enviada (bienvenida + tickets + reset) a {$user->email}");
            } else {
                //
                // —— COMPRA ADICIONAL —— solo envío de tickets adjuntos
                //

                $tickets = $order->purchasedTickets; // Collection de PurchasedTicket
                Mail::to($user->email)
                    ->send(new TicketsPurchasedMail($order, $tickets));

                Log::info("TicketsPurchasedMail enviada a {$user->email}");
            }

            // 4.3) Marcar la orden para no volver a enviar el email
            $order->update(['email_sent_at' => now()]);
        }

        // 5️⃣ Asignar rol “cliente” si no lo tiene
        if (! $user->hasRole('cliente')) {
            $user->assignRole('cliente');
        }

        // 6️⃣ Loguear al usuario inmediatamente para que acceda a “Mis Entradas”
        Auth::login($user);

        // 7️⃣ Redirigir al panel de entradas con mensaje de éxito
        return redirect()
            ->route('mis-entradas')
            ->with('success', '¡Compra exitosa! Tus entradas ya están disponibles, y te hemos enviado un enlace para configurar tu contraseña.');
    }

    public function failure(Request $request, Order $order)
    {
        //Log::warning('MercadoPago Failed redireccin para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.failure', compact('order'));
    }

    public function pending(Request $request, Order $order)
    {
        //Log::info('MercadoPago Pending redireccin para orden ' . $order->id, $request->all());
        $order->refresh();
        return view('purchase.pending', compact('order'));
    }
}

