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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('titulo', 25);
            $table->decimal('monto_inicial', 10, 2);
            $table->decimal('monto_actual', 10, 2); // Este bajará con cada abono de 'payment_debts'
            $table->date('fecha_vencimiento'); //
            $table->decimal('tasa_interes', 5, 2)->default(0.00); 
            $table->string('prioridad', 10)->default('media');
            $table->enum('estado', ['pendiente', 'pagada'])->default('pendiente');
            $table->string('icono', 255)->nullable();
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
