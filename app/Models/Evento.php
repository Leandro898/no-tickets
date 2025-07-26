<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\RefundMercadoPagoPayment;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'slug',
        'ubicacion',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'imagen',
        'estado',
        'organizador_id',
        'restringir_edad',
        'edad_min_hombres',
        'edad_min_mujeres',
        'requerir_dni',
        'has_seats',
    ];

    protected $casts = [
        // …otros casts…
        'has_seats' => 'boolean',
    ];

    // Para que Laravel haga Route Model Binding usando 'slug' en lugar de 'id'
    public function getRouteKeyName(): string
    {
        // si es admin/* o api/*, binding por ID; si no, por slug
        return request()->is('admin/*') || request()->is('api/*')
            ? 'id'
            : 'slug';
    }

    // Relaciones
    public function seats()
    {
        return $this->hasMany(Seat::class);
    }

    public function entradas()
    {
        return $this->hasMany(Entrada::class);
    }

    public function organizador()
    {
        return $this->belongsTo(User::class, 'organizador_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'event_id');
    }

    protected static function booted(): void
    {
        parent::booted();

        // 1) Generar slug único al crear o actualizar
        static::saving(function (Evento $evento) {
            if (empty($evento->slug) || $evento->isDirty('nombre')) {
                $base   = Str::slug($evento->nombre);
                $slug   = $base;
                $i      = 1;
                while (
                    Evento::where('slug', $slug)
                    ->where('id', '<>', $evento->id)
                    ->exists()
                ) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $evento->slug = $slug;
            }
        });

        // 2) Al eliminar un evento, despachar reembolsos de órdenes 'approved'
        static::deleting(function (Evento $evento) {
            Log::info("Iniciando eliminación evento ID {$evento->id}. Despachando reembolsos...");

            $orders = $evento->orders()
                ->where('payment_status', 'approved')
                ->get();

            if ($orders->isEmpty()) {
                Log::info("No hay órdenes 'approved' para el evento ID {$evento->id}.");
            } else {
                foreach ($orders as $order) {
                    RefundMercadoPagoPayment::dispatch($order);
                    Log::info("Reembolso programado para orden ID {$order->id}, MP Payment ID {$order->mp_payment_id}.");
                }
            }
        });
    }
}
