<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entrada extends Model
{
    protected $fillable = [
        'evento_id', 'nombre', 'descripcion', 'stock_inicial', 'stock_actual',
        'max_por_compra', 'precio', 'disponible_desde', 'disponible_hasta', 'tipo',
    ];

    public function evento(): BelongsTo {
        return $this->belongsTo(Evento::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($entrada) {
            // Si no viene stock_actual, lo igualamos a stock_inicial
            if (is_null($entrada->stock_actual)) {
                $entrada->stock_actual = $entrada->stock_inicial;
            }
        });
    }
    

}
