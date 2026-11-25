<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla de tokens de la API. Solo se guarda la huella (hash) de cada token, nunca el token en sí,
     * igual que se hace con las contraseñas. El tipo distingue los de sesión (caducan pronto) de los personales.
     */
    public function up(): void
    {
        Schema::create('tokens_api', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('tipo', 20);
            $table->string('token_hash', 64)->unique();
            $table->dateTime('ultimo_uso')->nullable();
            $table->dateTime('caduca')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Deshace la migración borrando la tabla.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens_api');
    }
};
