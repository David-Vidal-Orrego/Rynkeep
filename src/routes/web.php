<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ExploradorController;
use App\Http\Controllers\PapeleraController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EnlaceController;
use App\Http\Controllers\EnlacePublicoController;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/{carpeta?}', [ExploradorController::class, 'index'])->name('dashboard');

    Route::post('/archivos', [ArchivoController::class, 'store'])->name('archivos.store');
    Route::get('/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{archivo}', [ArchivoController::class, 'destroy'])->name('archivos.destroy');

    Route::post('/archivos/{archivo}/enlace', [EnlaceController::class, 'store'])->name('enlaces.store');
    Route::delete('/archivos/{archivo}/enlace', [EnlaceController::class, 'destroy'])->name('enlaces.destroy');
    Route::post('/carpetas/{carpeta}/enlace', [EnlaceController::class, 'storeCarpeta'])->name('enlaces.carpeta.store');
    Route::delete('/carpetas/{carpeta}/enlace', [EnlaceController::class, 'destroyCarpeta'])->name('enlaces.carpeta.destroy');

    Route::get('/papelera', [PapeleraController::class, 'index'])->name('papelera.index');
    Route::post('/papelera/{id}/restaurar', [PapeleraController::class, 'restaurar'])->name('papelera.restaurar');
    Route::delete('/papelera/{id}', [PapeleraController::class, 'eliminar'])->name('papelera.eliminar');
    Route::post('/papelera/carpetas/{id}/restaurar', [PapeleraController::class, 'restaurarCarpeta'])->name('papelera.carpeta.restaurar');
    Route::delete('/papelera/carpetas/{id}', [PapeleraController::class, 'eliminarCarpeta'])->name('papelera.carpeta.eliminar');

    Route::post('/carpetas', [CarpetaController::class, 'store'])->name('carpetas.store');
    Route::patch('/carpetas/{carpeta}', [CarpetaController::class, 'update'])->name('carpetas.update');
    Route::delete('/carpetas/{carpeta}', [CarpetaController::class, 'destroy'])->name('carpetas.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/idioma', [ProfileController::class, 'actualizarIdioma'])->name('profile.idioma');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.index');
    Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('usuarios.create');
    Route::post('/usuarios', [AdminController::class, 'guardarUsuario'])->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar', [AdminController::class, 'editarUsuario'])->name('usuarios.edit');
    Route::patch('/usuarios/{usuario}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [AdminController::class, 'eliminarUsuario'])->name('usuarios.destroy');
});

Route::get('/s/{token}', [EnlacePublicoController::class, 'mostrar'])
    ->where('token', '[A-Za-z0-9]{64}')->name('enlace.publico');
Route::post('/s/{token}/acceso', [EnlacePublicoController::class, 'acceso'])
    ->where('token', '[A-Za-z0-9]{64}')->name('enlace.acceso');
Route::get('/s/{token}/descargar', [EnlacePublicoController::class, 'descargar'])
    ->where('token', '[A-Za-z0-9]{64}')->name('enlace.descarga');
Route::get('/s/{token}/archivo/{archivo}', [EnlacePublicoController::class, 'descargarArchivo'])
    ->where('token', '[A-Za-z0-9]{64}')->where('archivo', '[0-9]+')->name('enlace.descarga.archivo');

require __DIR__ . '/auth.php';
