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
       Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            $table->foreignId('wallet_id')->nullable()->constrained()->onDelete('set null');

            $table->string("titulo", 25);
            $table->string("icono", 255)->nullable();
            
            // Valores Monetarios
            $table->decimal('monto_inicial', 10, 2)->default(0.00); // Lo que metió al principio
            $table->decimal('valor_actual', 10, 2)->default(0.00);  // Lo que vale hoy
            $table->decimal('ganancia', 10, 2)->default(0.00);      // Diferencia (Calculada o manual)
            $table->enum('tipo_renta', ['fija', 'variable'])->default('variable');
            $table->decimal('tasa_interes', 5, 2)->nullable(); // Ej: 12.50 para 12.5% anual
            $table->enum('estado', ['activa', 'finalizada', 'cancelada'])->default('activa');
            $table->date('fecha_adquisicion')->useCurrent(); // Cuándo empezó
            $table->date('fecha_vencimiento')->nullable();  // Cuándo termina (si aplica)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investments');
    }
};
