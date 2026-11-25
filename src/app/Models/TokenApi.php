<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TokenApi extends Model
{
    public const TIPO_SESION = 'sesion';

    public const TIPO_PERSONAL = 'personal';

    protected $table = 'tokens_api';

    protected $hidden = ['token_hash'];

    /**
     * Conversiones automáticas de las fechas.
     */
    protected function casts(): array
    {
        return [
            'caduca' => 'datetime',
            'ultimo_uso' => 'datetime',
        ];
    }

    /**
     * Crea un token para un usuario y devuelve el modelo guardado junto con el texto del token.
     * Ese texto solo existe en este momento: en la base de datos se guarda su huella, así que no se puede recuperar después.
     *
     * @return array{0: TokenApi, 1: string}
     */
    public static function generar(Usuario $usuario, string $nombre, string $tipo, ?Carbon $caduca): array
    {
        $texto = 'rk_'.Str::random(48);

        $token = new self();
        $token->forceFill([
            'usuario_id' => $usuario->id,
            'nombre' => mb_substr($nombre, 0, 100),
            'tipo' => $tipo,
            'token_hash' => hash('sha256', $texto),
            'caduca' => $caduca,
        ])->save();

        return [$token, $texto];
    }

    /**
     * Busca el token que corresponde a un texto recibido en la cabecera Authorization, comparando huellas.
     */
    public static function buscar(string $texto): ?self
    {
        return self::where('token_hash', hash('sha256', $texto))->first();
    }

    /**
     * Indica si el token ya ha pasado su fecha de caducidad. Los que no tienen fecha no caducan.
     */
    public function haCaducado(): bool
    {
        return $this->caduca !== null && $this->caduca->isPast();
    }

    /**
     * Apunta que el token se acaba de usar. Solo escribe en la base de datos si ha pasado más de un minuto
     * desde el último apunte, para no hacer una escritura en cada petición.
     */
    public function marcarUso(): void
    {
        if ($this->ultimo_uso === null || $this->ultimo_uso->lt(now()->subMinute())) {
            $this->forceFill(['ultimo_uso' => now()])->saveQuietly();
        }
    }

    /**
     * Usuario dueño del token.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
