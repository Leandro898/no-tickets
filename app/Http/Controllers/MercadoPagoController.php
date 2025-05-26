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
// Si estás usando un modelo MercadoPagoAccount para guardar los tokens, impórtalo también
// use App\Models\MercadoPagoAccount;

class MercadoPagoController extends Controller
{
    public function __construct()
    {
        // Establece el Access Token de TU PLATAFORMA (el que pusiste en .env)
        // Este token se usa para llamadas a la API que no son específicas de un vendedor (ej. consultar pagos en el webhook si no tienes el token del vendedor)
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
        // Asumiendo que has añadido el método hasMercadoPagoAccount() a tu modelo User
        if (Auth::user()->mp_access_token) { // O Auth::user()->hasMercadoPagoAccount() si lo implementaste
            return redirect()->route('mercadopago.status')->with('info', 'Tu cuenta de Mercado Pago ya está conectada.');
        }

        $clientId = config('mercadopago.client_id');
        // ¡CORRECCIÓN CLAVE AQUÍ! Genera una URL absoluta para el redirect_uri
        $redirectUri = route('mercadopago.callback', [], true); // El 'true' asegura que sea una URL completa (https://...)

        // Scopes (permisos) necesarios para una integración de Marketplace:
        $scopes = [
            'offline_access', // Para obtener un refresh_token y mantener la conexión a largo plazo.
            'read',           // Para leer información de la cuenta del vendedor (ej. pagos, datos de la cuenta).
            'write'           // Para realizar acciones en nombre del vendedor (ej. crear pagos, gestionar reembolsos).
        ];
        $scopesString = implode('%20', $scopes); // Formato requerido para la URL (espacios codificados como %20)

        // La URL de autorización de Mercado Pago
        // Nota: Mercado Pago usa 'authorization' o 'oauth/authorize'. Tu URL actual es correcta.
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

        // ¡CORRECCIÓN CLAVE AQUÍ! Genera una URL absoluta para el redirect_uri también en el callback
        $redirectUri = route('mercadopago.callback', [], true);
        $clientId = config('mercadopago.client_id');
        $clientSecret = config('mercadopago.client_secret');

        // Validar que las credenciales de la aplicación están configuradas en .env y config/mercadopago.php
        if (is_null($clientSecret) || is_null($clientId)) {
            Log::critical('Mercado Pago Client ID o Client Secret es NULL en el callback. Verifica tu archivo de configuración.');
            return redirect()->route('mercadopago.status')->with('error', 'Error de configuración: Credenciales de la aplicación no encontradas.');
        }

        Log::info('Mercado Pago OAuth Callback: Client ID: ' . $clientId);
        Log::info('Mercado Pago OAuth Callback: Client Secret cargado (parcial): ' . substr($clientSecret, 0, 5) . '...');
        Log::info('Mercado Pago OAuth Callback: Redirect URI: ' . $redirectUri);
        Log::info('Mercado Pago OAuth Callback: Código recibido: ' . $code);

        try {
            // No es necesario establecer el Access Token de la plataforma aquí,
            // ya que esta llamada es para intercambiar el código por el token del vendedor.
            // La librería de MP ya usa client_id y client_secret para esta operación.

            $oAuthClient = new OAuthClient();
            Log::info('Mercado Pago OAuth Callback: Instancia de OAuthClient creada.');

            $requestOAuth = new OAuthCreateRequest();
            $requestOAuth->code = $code;
            $requestOAuth->redirect_uri = $redirectUri;
            $requestOAuth->client_id = $clientId;
            $requestOAuth->client_secret = $clientSecret;
            $requestOAuth->grant_type = 'authorization_code';

            Log::info('Mercado Pago OAuth Request data BEFORE SENDING to Mercado Pago API:', [
                'code' => $requestOAuth->code,
                'redirect_uri' => $requestOAuth->redirect_uri,
                'client_id' => $requestOAuth->client_id,
                'client_secret' => substr($requestOAuth->client_secret, 0, 5) . '...',
                'grant_type' => $requestOAuth->grant_type
            ]);

            $response = $oAuthClient->create($requestOAuth);

            Log::info('Mercado Pago OAuth Callback: Respuesta de la API de Mercado Pago recibida con éxito.');
            Log::info('Mercado Pago OAuth Callback: Datos clave de la respuesta:', [
                'access_token_exists' => !empty($response->access_token),
                'refresh_token_exists' => !empty($response->refresh_token),
                'user_id' => $response->user_id ?? 'N/A',
                'expires_in' => $response->expires_in ?? 'N/A',
                'public_key_exists' => !empty($response->public_key),
            ]);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            if (!$user) {
                Log::error('Mercado Pago OAuth Callback: Usuario autenticado no encontrado. No se pudo guardar el token.');
                return redirect()->route('mercadopago.status')->with('error', 'No se pudo guardar la conexión. Usuario no autenticado.');
            }

            // Guarda los tokens y el user_id de Mercado Pago en tu base de datos (modelo User)
            $user->mp_access_token = $response->access_token;
            $user->mp_refresh_token = $response->refresh_token ?? null;
            $user->mp_public_key = $response->public_key ?? null;
            $user->mp_user_id = $response->user_id;
            $user->mp_expires_in = now()->addSeconds($response->expires_in);

            $user->save();

            Log::info('Mercado Pago OAuth Callback: Cuenta conectada y tokens guardados exitosamente para user_id: ' . $user->id . ' (MP User ID: ' . $user->mp_user_id . ')');
            return redirect()->route('mercadopago.status')->with('success', 'Cuenta de Mercado Pago conectada exitosamente.');

        } catch (MPApiException $e) {
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

        if (!$user->mp_access_token) { // O $user->hasMercadoPagoAccount()
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
        // Este webhook es para notificaciones generales de la plataforma.
        // La lógica de creación de tickets y actualización de stock la tenemos en CompraEntradaController.
        // Si este webhook recibe un 'payment' y necesitas procesarlo, puedes redirigir la lógica
        // o llamar a un servicio compartido.
        Log::info('Mercado Pago Webhook Received in MercadoPagoController:', $request->all());

        $type = $request->input('type');
        $dataId = $request->input('data.id');

        if ($type === 'payment' && $dataId) {
            try {
                $platformAccessToken = config('mercadopago.platform_access_token');
                if (is_null($platformAccessToken)) {
                    Log::critical('Webhook (MercadoPagoController): Mercado Pago Platform Access Token es NULL. No se puede consultar el pago.');
                    return response()->json(['status' => 'error', 'message' => 'Configuración de token de plataforma faltante.'], 500);
                }
                MercadoPagoConfig::setAccessToken($platformAccessToken); // Asegura que se usa el token de la plataforma para consultar

                $paymentClient = new \MercadoPago\Client\Payment\PaymentClient();
                $payment = $paymentClient->get($dataId);

                if ($payment->status === 200) {
                    Log::info('Webhook (MercadoPagoController): Payment fetched. Status: ' . $payment->status . ', External Ref: ' . ($payment->external_reference ?? 'N/A'));
                    // Aquí podrías tener lógica para actualizar el estado de órdenes si no lo hace CompraEntradaController
                    // O simplemente loguear y dejar que CompraEntradaController maneje la lógica principal.
                    return response()->json(['status' => 'success', 'message' => 'Webhook de pago procesado en MercadoPagoController.'], 200);
                } else {
                    Log::error('Webhook (MercadoPagoController): Error al obtener detalles del pago ' . $dataId . '. Status: ' . $payment->status);
                    return response()->json(['status' => 'error', 'message' => 'Error al consultar el pago en MP.'], 500);
                }
            } catch (MPApiException $e) {
                Log::error('Webhook (MercadoPagoController) MP API Error: ' . $e->getApiResponse()->getContent());
                return response()->json(['status' => 'error', 'message' => 'Error de API al consultar el pago.'], 500);
            } catch (\Exception $e) {
                Log::error('Webhook (MercadoPagoController) general Error: ' . $e->getMessage());
                return response()->json(['status' => 'error', 'message' => 'Error interno del servidor.'], 500);
            }
        }

        return response()->json(['status' => 'error', 'message' => 'Tipo de notificación o ID no reconocido en MercadoPagoController.'], 400);
    }
}
