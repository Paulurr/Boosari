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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
            ->constrained();
            $table->foreignId('wallet_origen_id')
            ->nullable()
            ->constrained('wallets');
            $table->foreignId('wallet_destino_id')
            ->nullable()
            ->constrained('wallets');
            $table->foreignId('category_id')
            ->nullable()
            ->constrained();
            $table->foreignId('income_id')
            ->nullable()
            ->constrained();
            $table->string("titulo",25);
            $table->string("icono",255)
            ->nullable();
            $table->decimal('monto',10,2);
            $table->enum('tipo',['ingreso','gasto','transferencia']);
            $table->timestamp('fecha_ejecucion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
