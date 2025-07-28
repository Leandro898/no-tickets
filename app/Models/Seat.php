<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'evento_id',
        'entrada_id',
        'row',
        'prefix',
        'number',
        'type',
        'width',
        'height',
        'radius',
        'label',
        'font_size',
        'x',
        'y',
        'rotation',
    ];
}
