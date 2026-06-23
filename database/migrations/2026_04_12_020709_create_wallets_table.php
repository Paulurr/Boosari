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
            $table->string("titulo",25);
            $table->string("icono",255)->nullable();
            $table->enum('tipo', [
                'ninguno',
                'efectivo',
                'debito',
                'credito',
                'ahorro'
            ])->default('ninguno');
            $table->decimal('monto_actual', 10, 2)->default(0.00);
            $table->decimal('monto_inicial', 10, 2)->default(0.00);
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
