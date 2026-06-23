<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_origen_id',
        'wallet_destino_id',
        'category_id',
        'income_id',
        'titulo',
        'icono',
        'monto',
        'tipo',
        'fecha_ejecucion',
    ];

    protected $casts = [
        'fecha_ejecucion' => 'datetime',
    ];

    // ==========================================
    // SECCIÓN DE RELACIONES (AÑADE ESTO ABAJO)
    // ==========================================

    /**
     * Relación con la billetera de origen
     */
    public function walletOrigen()
    {
        return $this->belongsTo(Wallet::class, 'wallet_origen_id');
    }

    /**
     * Relación con la billetera de destino
     */
    public function walletDestino()
    {
        return $this->belongsTo(Wallet::class, 'wallet_destino_id');
    }

    /**
     * Relación con la categoría
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}