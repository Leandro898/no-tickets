<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class PurchasedTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'entrada_id',
        'unique_code',
        'qr_path',
        'status',
        'scanned_at',
        'buyer_name',
        'ticket_type',
    ];


    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Una Entrada Comprada pertenece a una Orden de Compra.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Una Entrada Comprada pertenece a un Tipo de Entrada.
     */
    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class);
    }

    /**
     * Una Entrada Comprada pertenece a un Evento a través del modelo Entrada.
     */
    public function evento(): HasOneThrough
    {
        return $this->hasOneThrough(
            \App\Models\Evento::class, // modelo destino
            \App\Models\Entrada::class, // modelo intermediario
            'id',           // foreign key en Entrada (relación hacia evento)
            'id',           // foreign key en Evento
            'entrada_id',   // local key en PurchasedTicket (relación hacia Entrada)
            'evento_id'     // local key en Entrada (relación hacia Evento)
        );
    }
}
