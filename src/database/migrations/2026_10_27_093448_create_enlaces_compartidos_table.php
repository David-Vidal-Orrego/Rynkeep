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
        Schema::create('enlaces_compartidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archivo_id')->nullable()->unique()->constrained('archivos')->cascadeOnDelete();
            $table->foreignId('carpeta_id')->nullable()->unique()->constrained('carpetas')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('contrasena')->nullable();
            $table->dateTime('fecha_expiracion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enlaces_compartidos');
    }
};
