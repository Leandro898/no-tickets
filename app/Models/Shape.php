<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shape extends Model
{
    protected $fillable = [
        'evento_id',
        'type',
        'x',
        'y',
        'width',
        'height',
        'rotation',
        'label',
        'font_size',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class);
    }
}
