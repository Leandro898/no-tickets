<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    protected $fillable = [
        'x',
        'y',
        'row',
        'prefix',
        'number',
        'entrada_id',
        'evento_id',
        'label',
        'radius'
    ];
}
