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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string("titulo", 25);
            $table->string("icono", 255)->nullable();
            
            $table->decimal('monto_inicial', 10, 2)->default(0.00);
            $table->decimal('monto_actual', 10, 2)->default(0.00);
            $table->decimal('monto_objetivo', 10, 2)->default(0.00);
            
            $table->text('descripcion')->nullable();
            $table->date('fecha_limite');
            
            $table->enum('estado', ['activa', 'completada', 'expirada'])->default('activa');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
