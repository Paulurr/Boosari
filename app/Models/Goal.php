<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'titulo',
        'icono',
        'monto_inicial',
        'monto_actual',
        'monto_objetivo',
        'descripcion',
        'fecha_limite',
        'estado',
    ];

    protected $casts = [
        'fecha_limite'   => 'date',
        'monto_inicial'  => 'decimal:2',
        'monto_actual'   => 'decimal:2',
        'monto_objetivo' => 'decimal:2',
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
        return $this->hasMany(PaymentGoal::class);
    }
}