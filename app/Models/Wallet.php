<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'titulo',
        'icono',
        'tipo',
        'monto_actual',
        'monto_inicial'
    ];
}
