<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // 

class Goal extends Model
{
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
        'monto_inicial'  => 'decimal:2',
        'monto_actual'   => 'decimal:2',
        'monto_objetivo' => 'decimal:2',
        'fecha_limite'   => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}