<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guarda en la cuenta del usuario sus preferencias de apariencia, para que se vean igual en la web y en la API.
     * NULL significa usar el valor por defecto: tema sistema, letra nunito y tamaño normal.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->string('tema', 10)->nullable()->after('idioma');
            $table->string('letra', 10)->nullable()->after('tema');
            $table->string('tamano', 12)->nullable()->after('letra');
        });
    }

    /**
     * Deshace la migración quitando las tres columnas.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn(['tema', 'letra', 'tamano']);
        });
    }
};
