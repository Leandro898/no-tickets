<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Jobs\RefundMercadoPagoPayment; // Importa el Job
use Illuminate\Support\Facades\Log; // Para loguear

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'ubicacion',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'imagen',
        'estado',
        'organizador_id',
    ];

    public function entradas()
    {
        return $this->hasMany(Entrada::class);
    }

    public function organizador()
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }

    // --- NUEVA RELACIÓN (Ahora Evento tiene muchas Órdenes) ---
    public function orders()
    {
        return $this->hasMany(Order::class, 'event_id');
    }

    // Usaremos un "model event" para despachar el reembolso cuando un evento sea eliminado
    protected static function booted(): void
    {
        static::deleting(function (Evento $event) {
            Log::info("Iniciando proceso de eliminación para el evento ID: {$event->id}. Despachando reembolsos...");

            // Recuperar todas las órdenes asociadas a este evento que hayan sido 'approved'
            $ordersToRefund = $event->orders()->where('payment_status', 'approved')->get();

            if ($ordersToRefund->isEmpty()) {
                Log::info("No se encontraron órdenes 'approved' para reembolsar para el evento ID: {$event->id}.");
            } else {
                foreach ($ordersToRefund as $order) {
                    // Despachar el Job para cada orden.
                    // Esto agregará el job a la cola y se ejecutará en segundo plano.
                    RefundMercadoPagoPayment::dispatch($order);
                    Log::info("Job de reembolso despachado para la orden ID: {$order->id}, Payment ID MP: {$order->mp_payment_id} (Evento: {$event->id})");
                }
            }
        });
    }
}