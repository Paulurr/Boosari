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
            
            // 1. Relación obligatoria con la deuda (Con borrado en cascada)
            $table->foreignId('debt_id')
                ->constrained()
                ->onDelete('cascade');
                
            // 2. NUEVO: Relación con la billetera que financia el pago
            $table->foreignId('wallet_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            
            // 3. Relación opcional con categorías
            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->string("titulo", 25); // Ej: "Abono capital Enero", "Pago mínimo"
            
            // 4. CORREGIDO: Nullable para evitar errores si el usuario no sube comprobante/icono
            $table->string("icono", 255)->nullable(); 
            
            $table->decimal('monto', 10, 2)->default(0.00);
            
            // 5. Atributo estratégico para deudas (Tarjeta de crédito / Préstamos)
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
