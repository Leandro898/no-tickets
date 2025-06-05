<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MercadoPago\MercadoPagoConfig;
// === CAMBIO CLAVE AQUÍ: Importamos el cliente específico para reembolsos ===
use MercadoPago\Client\Payment\PaymentRefundClient; // El cliente específico para reembolsos
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class RefundMercadoPagoPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    /**
     * Define cuántas veces el job debe ser reintentado.
     * @var int
     */
    public int $tries = 3;

    /**
     * Tiempo en segundos antes de reintentar el job.
     * @var int[]
     */
    public array $backoff = [10];

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Re-configurar Mercado Pago Access Token si el Job se ejecuta en un proceso de trabajador separado
        $platformAccessToken = config('mercadopago.platform_access_token');
        if (is_null($platformAccessToken)) {
            Log::critical('Mercado Pago Access Token de Plataforma es NULL al intentar un reembolso.');
            throw new \Exception('Mercado Pago Access Token no configurado para reembolso.');
        }
        MercadoPagoConfig::setAccessToken($platformAccessToken);
        // Asegúrate de que el entorno esté correctamente configurado.
        MercadoPagoConfig::setRuntimeEnviroment(config('mercadopago.sandbox') ? MercadoPagoConfig::LOCAL : MercadoPagoConfig::SERVER);


        Log::info("Intentando reembolso para la orden ID: {$this->order->id}, Payment ID MP: {$this->order->mp_payment_id}");

        // Verificar si la orden ya fue reembolsada o está en un estado que no requiere reembolso.
        if ($this->order->payment_status === 'refunded' || $this->order->payment_status === 'cancelled') {
            Log::info("La orden ID: {$this->order->id} ya está en estado 'refunded' o 'cancelled'. No se necesita reembolso.", [
                'order_id' => $this->order->id,
                'current_status' => $this->order->payment_status
            ]);
            return; // No hacer nada si ya está reembolsada/cancelada
        }

        // Obtener el ID del pago de Mercado Pago.
        $mpPaymentId = $this->order->mp_payment_id;

        if (empty($mpPaymentId)) {
            Log::error("Orden ID: {$this->order->id} no tiene un mp_payment_id para reembolsar.");
            throw new \Exception("No se encontró MP Payment ID para la orden {$this->order->id}. No se puede reembolsar.");
        }

        try {
            // === CAMBIO CLAVE AQUÍ: Usamos PaymentRefundClient para el reembolso ===
            $refundClient = new PaymentRefundClient();

            // Llamamos al método refundTotal() para un reembolso completo.
            $refund = $refundClient->refundTotal($mpPaymentId);


            // Mercado Pago puede devolver 'approved' o 'pending' para el reembolso
            if (isset($refund->id) && $refund->status === 'approved') {
                Log::info("Reembolso exitoso y aprobado para el pago MP ID: {$mpPaymentId}, Refund ID: {$refund->id}");

                // Actualizar el estado de la orden en tu DB a 'refunded'
                $this->order->payment_status = 'refunded';
                $this->order->save();

                Log::info("Estado de la orden ID: {$this->order->id} actualizado a 'refunded'.");

                // Opcional: Notificar al usuario sobre el reembolso
                // Mail::to($this->order->buyer_email)->send(new RefundNotificationMail($this->order, $refund));

            } elseif (isset($refund->id) && $refund->status === 'pending') {
                Log::warning("Reembolso de Mercado Pago para el pago MP ID: {$mpPaymentId} está en estado 'pending'. Reintentando en un momento...", [
                    'refund_id' => $refund->id,
                    'status_detail' => $refund->status_detail ?? 'N/A'
                ]);
                // Si el reembolso está en estado 'pending', lanzamos una excepción para que Laravel reintente el job.
                throw new \Exception("El reembolso para el pago {$mpPaymentId} está pendiente. Reintentando.");

            } else {
                // Caso donde el reembolso no es 'approved' ni 'pending' (ej. 'rejected', 'failed', o sin id de reembolso)
                $errorMessage = isset($refund->status_detail) ? $refund->status_detail : 'Detalle de error no disponible.';
                Log::error("Reembolso fallido para el pago MP ID: {$mpPaymentId}. Status: " . ($refund->status ?? 'N/A') . ". Detalle: " . $errorMessage);
                throw new \Exception("El reembolso de Mercado Pago para el pago {$mpPaymentId} falló: {$errorMessage}");
            }

        } catch (MPApiException $e) {
            // Manejo de errores específicos de la API de Mercado Pago
            $apiErrorContent = $e->getApiResponse() ? $e->getApiResponse()->getContent() : 'No API response content';
            if (is_array($apiErrorContent) || is_object($apiErrorContent)) {
                $apiErrorContent = json_encode($apiErrorContent);
            } else {
                $apiErrorContent = (string) $apiErrorContent;
            }
            $statusCode = $e->getApiResponse() ? $e->getApiResponse()->getStatusCode() : 500;
            Log::error('MP API Error al intentar reembolso para MP Payment ID ' . $mpPaymentId . ': ' . $apiErrorContent, ['exception' => $e, 'status_code' => $statusCode]);
            throw $e;
        } catch (\Exception $e) {
            // Manejo de errores generales
            Log::error('Error general al intentar reembolso para MP Payment ID ' . $mpPaymentId . ': ' . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical("El Job de reembolso para la orden ID: {$this->order->id} falló después de varios reintentos: " . $exception->getMessage());

        try {
            $this->order->payment_status = 'refund_failed'; // Nuevo estado para indicar fallo
            $this->order->save();
            Log::info("Estado de la orden ID: {$this->order->id} actualizado a 'refund_failed' debido a fallo del job.");
        } catch (\Exception $e) {
            Log::error("Error al intentar actualizar el estado de la orden a 'refund_failed' para la orden ID: {$this->order->id}: " . $e->getMessage());
        }
    }
}
