<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'debts';

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'user_id',
        'titulo',
        'categoria',
        'monto_inicial',
        'monto_actual',
        'fecha_vencimiento',
        'tasa_interes',
        'prioridad',
        'estado',
        'icono',
    ];

    /**
     * Casts de atributos para facilitar su manipulación.
     */
    protected $casts = [
        'monto_inicial'     => 'decimal:2',
        'monto_actual'      => 'decimal:2',
        'tasa_interes'      => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    // --- RELACIONES ---

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(PaymentDebt::class, 'debt_id');
    }
}