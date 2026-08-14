<?php
// Ubicación sugerida: app/Http/Controllers/ConfiguracionController.php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ConfiguracionController extends Controller
{
    public function index(): View
    {
        $configuracion = Configuracion::firstOrNew(
            ['user_id' => auth()->id()],
            ['agente_activo' => true]
        );

        return view('config', [
            'configuracion' => $configuracion,
            'coloresClaro' => $configuracion->coloresClaroConFallback(),
            'coloresOscuro' => $configuracion->coloresOscuroConFallback(),
        ]);
    }

    public function actualizarAgente(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agente_activo' => ['required', 'boolean'],
        ]);

        $configuracion = Configuracion::updateOrCreate(
            ['user_id' => auth()->id()],
            ['agente_activo' => $data['agente_activo']]
        );

        return response()->json([
            'message' => 'Preferencia actualizada.',
            'agente_activo' => $configuracion->agente_activo,
        ]);
    }

    /**
     * Actualiza la paleta de un modo ('claro' u 'oscuro').
     * El modo viaja en el campo "modo" del body (ver input hidden en la vista).
     */
    public function actualizarColores(Request $request): JsonResponse
    {
        $modo = $request->input('modo', 'claro');
        abort_unless(in_array($modo, ['claro', 'oscuro']), 422, 'Modo de paleta inválido.');

        $sufijo = $modo === 'oscuro' ? '_oscuro' : '';
        $reglaColor = ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'];

        $reglas = [];
        for ($i = 1; $i <= 7; $i++) {
            $reglas["color_{$i}{$sufijo}"] = $reglaColor;
        }

        $data = $request->validate($reglas);

        $configuracion = Configuracion::updateOrCreate(
            ['user_id' => auth()->id()],
            $data
        );

        return response()->json([
            'message' => 'Paleta actualizada.',
            'modo' => $modo,
            'colores' => $modo === 'oscuro'
                ? $configuracion->coloresOscuroConFallback()
                : $configuracion->coloresClaroConFallback(),
        ]);
    }

    /**
     * Restaura a colores base la paleta de un modo. El modo viaja en
     * el query string: DELETE /config/colores?modo=oscuro
     */
    public function restaurarColores(Request $request): JsonResponse
    {
        $modo = $request->query('modo', 'claro');
        abort_unless(in_array($modo, ['claro', 'oscuro']), 422, 'Modo de paleta inválido.');

        $sufijo = $modo === 'oscuro' ? '_oscuro' : '';
        $reset = [];
        for ($i = 1; $i <= 7; $i++) {
            $reset["color_{$i}{$sufijo}"] = null;
        }

        Configuracion::where('user_id', auth()->id())->update($reset);

        return response()->json([
            'message' => 'Colores restaurados.',
            'modo' => $modo,
            'colores' => $modo === 'oscuro'
                ? Configuracion::coloresBaseOscuro()
                : Configuracion::coloresBaseClaro(),
        ]);
    }
}