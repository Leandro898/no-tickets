<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\OAuth\OAuthClient;
use MercadoPago\Client\OAuth\OAuthCreateRequest;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Asegúrate de tener este 'use' para tu modelo User

class MercadoPagoController extends Controller
{
    public function __construct()
    {
        // Establece el Access Token de TU PLATAFORMA (el que pusiste en .env)
        // Todas las llamadas a la API de MP que hagas desde este controlador
        // (a menos que uses un access_token específico de un vendedor)
        // usarán este token.
        $platformAccessToken = config('mercadopago.platform_access_token');

        if (is_null($platformAccessToken)) {
            Log::critical('Mercado Pago Access Token de Plataforma es NULL. La integración no funcionará correctamente.');
            // En un entorno de producción, aquí podrías lanzar una excepción o redirigir a una página de error de configuración.
            // throw new \Exception('Mercado Pago Platform Access Token no configurado.');
        }

        MercadoPagoConfig::setAccessToken($platformAccessToken);
        // Configura el entorno: true para Sandbox, false para Producción
        MercadoPagoConfig::setRuntimeEnviroment(config('mercadopago.sandbox') ? MercadoPagoConfig::LOCAL : MercadoPagoConfig::SERVER);
    }

    /**
     * Redirige al usuario a la página de autorización de Mercado Pago (OAuth).
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect()
    {
        // Asegúrate de que el usuario esté autenticado en tu plataforma antes de intentar conectar MP
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para conectar tu cuenta de Mercado Pago.');
        }

        // Si el usuario ya tiene una cuenta MP conectada, lo informamos y redirigimos a su estado
        if (Auth::user()->hasMercadoPagoAccount()) {
            return redirect()->route('mercadopago.status')->with('info', 'Tu cuenta de Mercado Pago ya está conectada.');
        }

        $clientId = config('mercadopago.client_id');
        // Usamos la ruta nombrada para generar la URL de callback, asegurando que sea correcta
        $redirectUri = route('mercadopago.callback');

        // Scopes (permisos) necesarios para una integración de Marketplace:
        // - offline_access: Para obtener un refresh_token y mantener la conexión a largo plazo.
        // - read: Para leer información de la cuenta del vendedor (ej. pagos, datos de la cuenta).
        // - write: Para realizar acciones en nombre del vendedor (ej. crear pagos, gestionar reembolsos).
        $scopes = [
            'offline_access',
            'read',
            'write'
        ];
        $scopesString = implode('%20', $scopes); // Formato requerido para la URL

        $authUrl = "https://auth.mercadopago.com/authorization?client_id={$clientId}&response_type=code&platform_id=mp&redirect_uri={$redirectUri}&scope={$scopesString}";

        Log::info('Mercado Pago Connect: Redirigiendo a URL de autorización: ' . $authUrl);

        try {
            return redirect()->away($authUrl); // Redirige a una URL externa
        } catch (\Exception $e) {
            Log::error('Error al generar URL de autorización de Mercado Pago: ' . $e->getMessage());
            return redirect()->route('dashboard')->with('error', 'No se pudo iniciar el proceso de conexión con Mercado Pago.');
        }
    }

    /**
     * Maneja el callback de Mercado Pago después de la autorización OAuth.
     * Aquí se intercambia el código de autorización por los tokens de acceso del vendedor.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        Log::info('Mercado Pago OAuth Callback: Inicio del proceso.');
        Log::info('Mercado Pago OAuth Callback: Request all data:', $request->all());

        $code = $request->input('code');

        // Manejo de errores si el usuario deniega la autorización o hay un error de MP
        if ($request->has('error') || $request->has('error_description')) {
            $error = $request->input('error');
            $errorDescription = $request->input('error_description');
            Log::warning('Mercado Pago OAuth Callback: Autorización denegada o error. Error: ' . $error . ' - Descripción: ' . $errorDescription);
            return redirect()->route('mercadopago.status')->with('error', 'Autorización de Mercado Pago denegada o con error: ' . ($errorDescription ?: 'Error desconocido.'));
        }

        // Verificar si se recibió el código de autorización (esencial para continuar)
        if (!$code) {
            Log::error('Mercado Pago OAuth Callback: No se recibió el código de autorización.');
            return redirect()->route('mercadopago.status')->with('error', 'No se pudo conectar la cuenta de Mercado Pago. Motivo: Código de autorización no recibido.');
        }

        $redirectUri = route('mercadopago.callback');
        $clientId = config('mercadopago.client_id');
        $clientSecret = config('mercadopago.client_secret');

        // Validar que las credenciales de la aplicación están configuradas en .env y config/mercadopago.php
        if (is_null($clientSecret) || is_null($clientId)) {
            Log::critical('Mercado Pago Client ID o Client Secret es NULL en el callback. Verifica tu archivo de configuración.');
            return redirect()->route('mercadopago.status')->with('error', 'Error de configuración: Credenciales de la aplicación no encontradas.');
        }

        Log::info('Mercado Pago OAuth Callback: Client ID: ' . $clientId);
        Log::info('Mercado Pago OAuth Callback: Client Secret cargado (parcial): ' . substr($clientSecret, 0, 5) . '...'); // Log para depuración, ocultando el secret completo
        Log::info('Mercado Pago OAuth Callback: Redirect URI: ' . $redirectUri);
        Log::info('Mercado Pago OAuth Callback: Código recibido: ' . $code);

        try {
            $oAuthClient = new OAuthClient(); // Instancia el cliente OAuth de Mercado Pago
            Log::info('Mercado Pago OAuth Callback: Instancia de OAuthClient creada.');

            // Prepara la solicitud para intercambiar el código por los tokens de acceso y refresco
            $requestOAuth = new OAuthCreateRequest();
            $requestOAuth->code = $code;
            $requestOAuth->redirect_uri = $redirectUri;
            $requestOAuth->client_id = $clientId;
            $requestOAuth->client_secret = $clientSecret;
            $requestOAuth->grant_type = 'authorization_code'; // Tipo de concesión para OAuth

            Log::info('Mercado Pago OAuth Request data BEFORE SENDING to Mercado Pago API:', [
                'code' => $requestOAuth->code,
                'redirect_uri' => $requestOAuth->redirect_uri,
                'client_id' => $requestOAuth->client_id,
                'client_secret' => substr($requestOAuth->client_secret, 0, 5) . '...',
                'grant_type' => $requestOAuth->grant_type
            ]);

            // Realiza la llamada a la API de Mercado Pago
            $response = $oAuthClient->create($requestOAuth);

            Log::info('Mercado Pago OAuth Callback: Respuesta de la API de Mercado Pago recibida con éxito.');
            Log::info('Mercado Pago OAuth Callback: Datos clave de la respuesta:', [
                'access_token_exists' => !empty($response->access_token),
                'refresh_token_exists' => !empty($response->refresh_token),
                'user_id' => $response->user_id ?? 'N/A', // El user_id de MP del vendedor
                'expires_in' => $response->expires_in ?? 'N/A', // Tiempo en segundos hasta que el token expire
                'public_key_exists' => !empty($response->public_key), // Clave pública del vendedor para su frontend
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user(); // Obtén el usuario actualmente autenticado en tu aplicación

            if (!$user) {
                Log::error('Mercado Pago OAuth Callback: Usuario autenticado no encontrado. No se pudo guardar el token.');
                return redirect()->route('mercadopago.status')->with('error', 'No se pudo guardar la conexión. Usuario no autenticado.');
            }

            // Guarda los tokens y el user_id de Mercado Pago en tu base de datos (modelo User)
            $user->mp_access_token = $response->access_token;
            $user->mp_refresh_token = $response->refresh_token ?? null;
            $user->mp_public_key = $response->public_key ?? null;
            $user->mp_user_id = $response->user_id;
            $user->mp_expires_in = now()->addSeconds($response->expires_in); // Calcula la fecha de expiración

            $user->save(); // Persiste los cambios en la base de datos

            Log::info('Mercado Pago OAuth Callback: Cuenta conectada y tokens guardados exitosamente para user_id: ' . $user->id . ' (MP User ID: ' . $user->mp_user_id . ')');
            return redirect()->route('mercadopago.status')->with('success', 'Cuenta de Mercado Pago conectada exitosamente.');

        } catch (MPApiException $e) {
            // Captura y maneja errores específicos de la API de Mercado Pago
            $apiResponseContent = $e->getApiResponse()->getContent();
            $apiStatusCode = $e->getApiResponse()->getStatusCode();

            Log::error('Mercado Pago OAuth Callback API Error: Status ' . $apiStatusCode . ' - Content: ' . json_encode($apiResponseContent));

            $errorMessage = 'Error de API al conectar con Mercado Pago: ';
            if (isset($apiResponseContent['message'])) {
                $errorMessage .= $apiResponseContent['message'];
            } elseif (isset($apiResponseContent['error_description'])) {
                $errorMessage .= $apiResponseContent['error_description'];
            } else {
                $errorMessage .= 'Status ' . $apiStatusCode . ' - Error desconocido.';
            }

            return redirect()->route('mercadopago.status')->with('error', $errorMessage);
        } catch (\Exception $e) {
            // Captura y maneja cualquier otro error general
            Log::error('Mercado Pago OAuth Callback: Error general al conectar con Mercado Pago: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->route('mercadopago.status')->with('error', 'Error inesperado al conectar con Mercado Pago: ' . $e->getMessage());
        }
    }

    /**
     * Desvincula la cuenta de Mercado Pago de un usuario (elimina los tokens de la DB).
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unlinkMPAccount(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para desvincular tu cuenta de Mercado Pago.');
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user->hasMercadoPagoAccount()) {
            return redirect()->route('mercadopago.status')->with('info', 'Tu cuenta de Mercado Pago no está conectada.');
        }

        try {
            // Opcional: Si Mercado Pago ofrece una API para revocar el token de forma remota,
            // la llamarías aquí. Actualmente, solo limpiamos los datos locales.
            $user->mp_access_token = null;
            $user->mp_refresh_token = null;
            $user->mp_public_key = null;
            $user->mp_user_id = null;
            $user->mp_expires_in = null;
            $user->save();

            Log::info('Mercado Pago Unlink: Cuenta desvinculada exitosamente para user_id: ' . $user->id);
            return redirect()->route('mercadopago.status')->with('success', 'Tu cuenta de Mercado Pago ha sido desvinculada.');
        } catch (\Exception $e) {
            Log::error('Error al desvincular la cuenta de Mercado Pago para user_id: ' . $user->id . ' - ' . $e->getMessage());
            return redirect()->route('mercadopago.status')->with('error', 'Error al desvincular tu cuenta de Mercado Pago.');
        }
    }

    /**
     * Maneja las notificaciones de Webhook/IPN de Mercado Pago.
     * Esta URL debe ser configurada en tu panel de Mercado Pago.
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request)
    {
        // El webhook no necesita autenticación de usuario, ya que Mercado Pago lo llama directamente.
        // Para mayor seguridad, podrías implementar un middleware para validar la firma de la notificación.
        Log::info('Mercado Pago Webhook Received:', $request->all());

        $type = $request->input('type'); // 'payment', 'merchant_order', etc.
        $dataId = $request->input('data.id'); // ID de la entidad (ej. payment_id)

        if ($type === 'payment' && $dataId) {
            try {
                // Para consultar el pago, se utiliza el Access Token de TU PLATAFORMA.
                // Ya está configurado globalmente en el constructor, no es necesario volver a establecerlo aquí.
                $paymentClient = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $paymentClient->get($dataId);

                Log::info('Webhook: Payment fetched. Status: ' . $payment->status . ', External Reference: ' . ($payment->external_reference ?? 'N/A'));

                // **Aquí es donde procesas el estado del pago y actualizas tu base de datos.**
                // Si ya generaste los tickets en el `processPayment` (cuando el pago es aprobado),
                // este webhook te servirá para:
                // 1. Manejar cambios de estado (ej. de PENDING a APPROVED).
                // 2. Manejar reembolsos o contracargos.

                switch ($payment->status) {
                    case 'approved':
                        // El pago fue aprobado.
                        // Aquí, verifica si ya habías procesado este pago y generado los tickets.
                        // Usa `payment->id` o `payment->external_reference` para buscar en tu DB.
                        // Si no se procesó, genera los tickets y actualiza el stock.
                        Log::info("Webhook: Payment {$payment->id} approved. External Ref: {$payment->external_reference}");
                        // Example: Update your local order/ticket status to 'approved'
                        // $order = Order::where('mp_payment_id', $payment->id)->first();
                        // if ($order && $order->status !== 'approved') {
                        //     $order->status = 'approved';
                        //     $order->save();
                        //     // Lógica para generar tickets aquí si NO SE HIZO en processPayment
                        // }
                        break;
                    case 'pending':
                        // El pago está pendiente (ej. pago en efectivo, tarjeta con validación adicional).
                        // Actualiza el estado de tu orden/ticket a 'pendiente'.
                        Log::info("Webhook: Payment {$payment->id} pending. External Ref: {$payment->external_reference}");
                        break;
                    case 'rejected':
                    case 'cancelled':
                        // El pago fue rechazado o cancelado.
                        // Actualiza el estado de tu orden/ticket a 'rechazado'/'cancelado'.
                        // Si ya habías generado tickets o reservado stock, deberías anularlos o devolver el stock.
                        Log::info("Webhook: Payment {$payment->id} rejected/cancelled. External Ref: {$payment->external_reference}");
                        break;
                    // Puedes añadir más casos para otros estados como 'in_process', 'refunded', 'charged_back', etc.
                    default:
                        Log::info("Webhook: Payment {$payment->id} has status {$payment->status}. External Ref: {$payment->external_reference}");
                        break;
                }

                return response()->json(['status' => 'success', 'message' => 'Webhook procesado.'], 200);

            } catch (MPApiException $e) {
                Log::error('Webhook MP API Error: ' . $e->getApiResponse()->getContent());
                return response()->json(['status' => 'error', 'message' => 'Error al consultar el pago en MP.'], 500);
            } catch (\Exception $e) {
                Log::error('Webhook general Error: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Error interno del servidor.'], 500);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Tipo de notificación o ID no reconocido.'], 400);
    }
}