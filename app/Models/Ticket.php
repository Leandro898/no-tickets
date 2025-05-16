<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    protected $fillable = [
        'entrada_id', 'nombre', 'email', 'estado', 'codigo_qr',
    ];

    public function entrada(): BelongsTo
    {
        return $this->belongsTo(Entrada::class);
    }
}
