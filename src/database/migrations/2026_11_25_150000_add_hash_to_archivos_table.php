<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Añade a los archivos la huella de su contenido (SHA-256, en hexadecimal) y la fecha original del archivo
     * en el equipo de quien lo subió. La huella vale NULL en los archivos antiguos hasta que se calcula con
     * rynkeep:calcular-hashes. La fecha original es opcional y solo informa: la sincronización usa updated_at.
     */
    public function up(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->string('hash', 64)->nullable()->after('tipo_archivo');
            $table->dateTime('modificado_original')->nullable()->after('hash');
        });
    }

    /**
     * Deshace la migración quitando las dos columnas.
     */
    public function down(): void
    {
        Schema::table('archivos', function (Blueprint $table) {
            $table->dropColumn(['hash', 'modificado_original']);
        });
    }
};
