<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'wallet_id',
        'titulo',
        'icono',
        'monto_inicial',
        'valor_actual',
        'ganancia',
        'tipo_renta',
        'tasa_interes',
        'estado',
        'fecha_adquisicion',
        'fecha_vencimiento',
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
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}