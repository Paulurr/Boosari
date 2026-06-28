<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Importamos la clase de relación

class PaymentDebt extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'wallet_id',
        'category_id', // Asegúrate de tenerlo en el fillable
        'titulo',
        'icono',
        'monto',
        'pago_minimo'
    ];

    /**
     * Relación con la Categoría del abono
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Category::class);
    }

    /**
     * Relación con la Deuda principal (por si acaso no la tenías)
     */
    public function debt(): BelongsTo
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Relación con la Billetera (por si acaso no la tenías)
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}