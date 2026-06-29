<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PaymentGoal extends Model
{
    //
    protected $fillable = [
        'goal_id', // o 'goal-id' según tu migración definitiva
        'wallet_id',
        'category_id',
        'titulo',
        'icono',
        'monto'
    ];
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'goal_id');
    }

    /**
     * Relación con la Billetera desde donde salió el dinero (opcional pero recomendada)
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Relación con la Categoría (opcional pero recomendada)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
