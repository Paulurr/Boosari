<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
 
            // Switch para activar/desactivar la página del agente IA.
            $table->boolean('agente_activo')->default(true);
 
            // Paleta de 7 colores (coinciden con --col1..--col7 de app.css).
            // Se dejan nullable a propósito: si el usuario no ha personalizado
            // un color, el campo queda en null y el modelo aplica el color base.
            $table->string('color_1', 7)->nullable();
            $table->string('color_2', 7)->nullable();
            $table->string('color_3', 7)->nullable();
            $table->string('color_4', 7)->nullable();
            $table->string('color_5', 7)->nullable();
            $table->string('color_6', 7)->nullable();
            $table->string('color_7', 7)->nullable();
 
            $table->timestamps();
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('configuraciones');
    }
};