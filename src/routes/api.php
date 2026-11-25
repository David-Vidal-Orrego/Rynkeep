<?php

use App\Http\Controllers\Api\ArchivosController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CambiosController;
use App\Http\Controllers\Api\CarpetasController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('api.token')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [UsuarioController::class, 'me']);

    Route::get('/files', [ArchivosController::class, 'index']);
    Route::get('/files/download', [ArchivosController::class, 'descargar']);
    Route::post('/files/upload', [ArchivosController::class, 'subir']);
    Route::patch('/files', [ArchivosController::class, 'actualizar']);
    Route::delete('/files', [ArchivosController::class, 'borrar']);

    Route::post('/folders', [CarpetasController::class, 'crear']);

    Route::get('/changes', [CambiosController::class, 'index']);
});
