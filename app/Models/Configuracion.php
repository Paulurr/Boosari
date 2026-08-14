<?php
// Ubicación sugerida: app/Models/Configuracion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = [
        'user_id',
        'agente_activo',
        'color_1', 'color_2', 'color_3', 'color_4', 'color_5', 'color_6', 'color_7',
        'color_1_oscuro', 'color_2_oscuro', 'color_3_oscuro', 'color_4_oscuro',
        'color_5_oscuro', 'color_6_oscuro', 'color_7_oscuro',
    ];

    protected $casts = [
        'agente_activo' => 'boolean',
    ];

    /** Colores base del modo claro (mismos de :root en app.css). */
    public const COLORES_BASE_CLARO = [
        1 => '#ffffff',
        2 => '#fff9e8',
        3 => '#ffc400',
        4 => '#22a443',
        5 => '#23803b',
        6 => '#252525',
        7 => '#3e3e3e',
    ];

    /** Colores base del modo oscuro (mismos de temaOscuro en ThemeMode.js). */
    public const COLORES_BASE_OSCURO = [
        1 => '#252525',
        2 => '#3e3e3e',
        3 => '#22a443',
        4 => '#ffc400',
        5 => '#23803b',
        6 => '#fff9e8',
        7 => '#ffffff',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Colores del modo claro [1 => '#hex', ..., 7 => '#hex'], rellenando
     * con el color base cualquier posición que el usuario no personalizó.
     */
    public function coloresClaroConFallback(): array
    {
        $resultado = [];
        foreach (self::COLORES_BASE_CLARO as $indice => $colorBase) {
            $campo = "color_{$indice}";
            $resultado[$indice] = $this->{$campo} ?: $colorBase;
        }
        return $resultado;
    }

    /** Igual que coloresClaroConFallback() pero para el modo oscuro. */
    public function coloresOscuroConFallback(): array
    {
        $resultado = [];
        foreach (self::COLORES_BASE_OSCURO as $indice => $colorBase) {
            $campo = "color_{$indice}_oscuro";
            $resultado[$indice] = $this->{$campo} ?: $colorBase;
        }
        return $resultado;
    }

    public static function coloresBaseClaro(): array
    {
        return self::COLORES_BASE_CLARO;
    }

    public static function coloresBaseOscuro(): array
    {
        return self::COLORES_BASE_OSCURO;
    }
}