<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArchivoController;
use App\Http\Controllers\ExploradorController;
use App\Http\Controllers\PapeleraController;

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [ExploradorController::class, 'index'])->name('dashboard');

    # Archivos
    Route::post('/archivos', [ArchivoController::class, 'store'])->name('archivos.store');
    Route::get('/archivos/{archivo}/descargar', [ArchivoController::class, 'descargar'])->name('archivos.descargar');
    Route::delete('/archivos/{archivo}', [ArchivoController::class, 'destroy'])->name('archivos.destroy');

    # Papelera
    Route::get('/papelera', [PapeleraController::class, 'index'])->name('papelera.index');
    Route::post('/papelera/{id}/restaurar', [PapeleraController::class, 'restaurar'])->name('papelera.restaurar');
    Route::delete('/papelera/{id}', [PapeleraController::class, 'eliminar'])->name('papelera.eliminar');
});
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__ . '/auth.php';
