<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany; // Importar HasMany


class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Una Orden de Compra tiene muchas Entradas Compradas (PurchasedTicket).
     */
    public function purchasedTickets(): HasMany // Tipo de retorno explícito
    {
        return $this->hasMany(PurchasedTicket::class);
    }

    /**
     * Una Orden de Compra pertenece a un Evento.
     */
    public function event(): BelongsTo // Tipo de retorno explícito
    {
        return $this->belongsTo(Evento::class, 'event_id');
    }

    /**
     * Relacion de Order con User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacion de Order con Ticket
     */
 
}  