<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importar BelongsTo

class PurchasedTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'entrada_id',
        'unique_code',
        'qr_path',
        'status',
        'scanned_at', // si lo asignas programáticamente en algún momento
        // Si tienes un 'scanned_by_user_id', también debería ir aquí
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * Una Entrada Comprada pertenece a una Orden de Compra.
     */
    public function order(): BelongsTo // Tipo de retorno explícito
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Una Entrada Comprada pertenece a un Tipo de Entrada (tu modelo Entrada).
     */
    public function entrada(): BelongsTo // ¡Relación con tu modelo Entrada!
    {
        return $this->belongsTo(Entrada::class);
    }
}
