<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Income extends Model
{
        use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'category_id',
        'titulo',
        'icono',
        'monto',
        'frecuencia',
        'fecha_inicio',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'activo' => 'boolean',
        'monto' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
