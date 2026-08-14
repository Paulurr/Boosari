<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentDebt extends Model
{
    use HasFactory;

    protected $table = 'payment_debts';

    protected $fillable = [
        'debt_id',
        'wallet_id',
        'category_id',
        'titulo',
        'icono',
        'monto',
        'pago_minimo',
    ];

    protected $casts = [
        'monto'       => 'decimal:2',
        'pago_minimo' => 'boolean',
    ];

    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}