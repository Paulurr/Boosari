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
        Schema::create('payment_debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')
            ->constrained();
            $table->foreignId('category_id')
            ->nullable()
            ->constrained();
            $table->string("titulo",25);
            $table->string("icono",255);
            $table->decimal('monto', 10, 2)->default(0.00);
            $table->boolean('pago_minimo')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_debts');
    }
};
