<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Income;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessRecurringIncome extends Command
{
    protected $signature = 'app:process-recurring-income';
    protected $description = 'Procesa los ingresos recurrentes pendientes';

    public function handle()
    {
        // Buscamos ingresos activos cuya fecha planificada ya llegó o es menor/igual a "ahora"
        $incomes = Income::where('activo', true)
            ->where('frecuencia', '!=', 'ninguno')
            ->where('fecha_inicio', '<=', now())
            ->get();

        foreach ($incomes as $income) {
            DB::beginTransaction();

            try {
                // Guardamos el momento exacto en el que DEBIÓ ejecutarse
                $fechaPlanificada = Carbon::parse($income->fecha_inicio);

                Transaction::create([
                    'user_id'          => $income->user_id,
                    'wallet_origen_id' => null,
                    'wallet_destino_id'=> $income->wallet_id,
                    'category_id'      => $income->category_id,
                    'income_id'        => $income->id,
                    'tipo'             => 'ingreso',
                    'titulo'           => $income->titulo,
                    'monto'            => $income->monto,
                    'icono'            => $income->icono,
                    'fecha_ejecucion'  => $fechaPlanificada // <-- Registra la fecha de la agenda del Income
                ]);

                if ($income->wallet_id) {
                    $wallet = Wallet::findOrFail($income->wallet_id);
                    $wallet->increment('monto_actual', $income->monto);
                }

                // Adelantamos la fecha del Income para el siguiente ciclo
                $income->fecha_inicio = $this->avanzarFecha($fechaPlanificada, $income->frecuencia);
                $income->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Error en ID {$income->id}: " . $e->getMessage());
            }
        }
    }

    private function avanzarFecha($fecha, $frecuencia) {
        switch ($frecuencia) {
            case 'diario': return $fecha->addDay();
            case 'semanal': return $fecha->addWeek();
            case 'quincenal': return $fecha->addDays(15);
            case 'mensual': return $fecha->addMonth();
            case 'anual': return $fecha->addYear();
            default: return $fecha;
        }
    }
}