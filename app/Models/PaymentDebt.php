<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDebt extends Model
{
    use HasFactory;

    protected $table = 'payment_debts';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'debt_id',
        'wallet_id',
        'category_id',
        'titulo',
        'icono',
        'monto',
        'pago_minimo',
    ];

    /**
     * Cast de atributos.
     */
    protected $casts = [
        'pago_minimo' => 'boolean',
        'monto' => 'decimal:2',
    ];

    // --- RELACIONES ---

    public function debt()
    {
        return $this->belongsTo(Debt::class, 'debt_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
}