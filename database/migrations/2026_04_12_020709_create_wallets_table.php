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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained();
            $table->enum('tipo', [
                'efectivo',
                'debito',
                'credito',
                'ahorro'
            ])->default('efectivo');
            $table->decimal('saldo_actual', 10, 2)->default(0.00);
            $table->decimal('valor_inicial', 10, 2)->default(0.00);
            $table->date('fecha_inicio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
