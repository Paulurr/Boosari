<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'categoria',
        'monto_inicial',
        'monto_actual',
        'wallet_id',
        'tipo_renta',
        'tasa_interes',
        'fecha_vencimiento',
        'imagen'
    ];

    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con la billetera de origen
    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}