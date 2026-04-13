<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained();
            $table->decimal('monto_total', 10, 2)->default(1.00);
            $table->decimal('saldo_actual', 10, 2)->default(0.00);
            $table->text('descripcion')->nullable();
            $table->decimal('tasa_interes', 7, 4)->default(0.0000);
            $table->date('fecha_inicio');
            $table->date('fecha_limite');
            $table->enum('estado', [
                'pendiente',
                'pagando',
                'pagada'
            ])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
