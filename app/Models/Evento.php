<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evento extends Model
{
    protected $fillable = [
        'nombre', 'ubicacion', 'fecha_inicio', 'fecha_fin', 'descripcion', 'imagen', 'estado',
    ];

    public function entradas(): HasMany {
        return $this->hasMany(Entrada::class);
    }
}