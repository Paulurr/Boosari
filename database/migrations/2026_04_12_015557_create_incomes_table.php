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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained();
            $table->foreignId('wallet_id')->nullable()
            ->constrained();
            $table->foreignId('category_id')->nullable()
            ->constrained();
            $table->string("titulo",25);
            $table->string("icono",255)->nullable();
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->enum('frecuencia', [
                'ninguno',
                'diario',
                'semanal',
                'quincenal',
                'mensual',
                'anual'
            ])->default('ninguno');
            $table->timestamp('fecha_inicio')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
