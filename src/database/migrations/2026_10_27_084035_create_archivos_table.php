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
        Schema::create('archivos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('carpeta_id')->nullable()->constrained('carpetas')->cascadeOnDelete();
            $table->string('nombre_original', 255);
            $table->string('ruta_almacenamiento', 255)->unique();
            $table->unsignedBigInteger('tamano');
            $table->string('tipo_archivo', 100);
            $table->dateTime('papelera')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archivos');
    }
};
