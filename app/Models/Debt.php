<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'titulo',
        'monto_inicial',
        'monto_actual',
        'fecha_vencimiento',
        'tasa_interes',
        'prioridad',
        'estado',
        'icono',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'monto_inicial'     => 'decimal:2',
        'monto_actual'      => 'decimal:2',
        'tasa_interes'      => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentDebt::class);
    }
}