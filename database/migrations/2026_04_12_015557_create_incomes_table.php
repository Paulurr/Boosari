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
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->enum('frecuencia', [
                'ninguna',
                'diario',
                'semanal',
                'quincenal',
                'mensual',
                'anual'
            ])->default('ninguna');
            $table->string('descripcion', 100)->nullable();
            $table->date('fecha')->nullable();
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
