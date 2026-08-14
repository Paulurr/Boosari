<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGoal extends Model
{
    use HasFactory;

    protected $table = 'payment_goals';

    protected $fillable = [
        'goal_id',
        'wallet_id',
        'category_id',
        'titulo',
        'icono',
        'monto',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
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