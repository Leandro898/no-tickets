<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MagicLink extends Model
{
    protected $fillable = ['email', 'token', 'expires_at'];
    public $timestamps = true;
}
