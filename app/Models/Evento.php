<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'ubicacion', 'fecha_inicio', 'fecha_fin', 'descripcion', 'imagen', 'estado','organizador_id',
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
}