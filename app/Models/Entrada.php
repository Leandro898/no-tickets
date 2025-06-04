<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entrada extends Model
{
    use HasFactory;

    protected $fillable = [
        'evento_id',
        'nombre',
        'descripcion',
        'stock_inicial',
        'stock_actual',
        'max_por_compra',
        'precio',
        'disponible_desde',
        'disponible_hasta',
        'tipo',
    ];

    // Añadimos los casts para asegurar tipos de datos correctos, especialmente para fechas y decimales
    protected $casts = [
        'precio' => 'decimal:2', // Asumiendo que 'precio' es tu columna decimal
        'disponible_desde' => 'datetime',
        'disponible_hasta' => 'datetime',
        // Otros campos numéricos si quieres cast:
        'stock_inicial' => 'integer',
        'stock_actual' => 'integer',
        'max_por_compra' => 'integer',
    ];

    public function evento(): BelongsTo
    {
        return $this->belongsTo(Evento::class);
    }

    // --- NUEVA RELACIÓN: Una Entrada (tipo de entrada) tiene muchas Entradas Compradas (PurchasedTicket) ---
    public function purchasedTickets(): HasMany // ¡Aquí la nueva relación!
    {
        return $this->hasMany(PurchasedTicket::class, 'entrada_id'); // Aseguramos la clave foránea
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

    protected static function booted()
    {
        static::creating(function ($entrada) {
            if (is_null($entrada->stock_inicial)) {
                $entrada->stock_inicial = $entrada->stock_actual;
            }
        });
    }
}
