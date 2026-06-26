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
        Schema::create('payment_goals', function (Blueprint $table) {
            $table->id();
            
            // Relación obligatoria con la meta (con borrado en cascada)
            $table->foreignId('goal_id')->constrained()->onDelete('cascade');
            
            $table->foreignId('wallet_id')->nullable()->constrained()->onDelete('set null');
            
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            $table->string("titulo", 25); // Ej: "Aporte mensual", "Guardado de vuelto"
            $table->string("icono", 255)->nullable(); // Nullable por seguridad si usas uno genérico
            
            $table->decimal('monto', 10, 2)->default(0.00); // Cantidad de dinero aportada
            
            $table->timestamps(); // Registra automáticamente 'created_at' (que sirve como fecha del abono)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_goals');
    }
};
