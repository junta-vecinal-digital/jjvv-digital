<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocioController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin,presidente,tesorero'])->group(function () {
    Route::get('/finanzas', function () {
        return 'Acceso permitido: módulo financiero (solo Admin, Presidente o Tesorero)';
    })->name('finanzas.index');
});

Route::middleware(['auth', 'role:vecino'])->group(function () {
    Route::get('/mi-panel', function () {
        return 'Acceso permitido: panel del vecino';
    })->name('vecino.panel');
});

// Vecino: solicitar inscripción como socio
Route::middleware(['auth', 'role:vecino'])->group(function () {
    Route::get('/socios/solicitud', [SocioController::class, 'create'])->name('socios.create');
    Route::post('/socios/solicitud', [SocioController::class, 'store'])->name('socios.store');
});

// Dirigentes: revisar y gestionar socios
Route::middleware(['auth', 'role:admin,presidente,secretario'])
    ->prefix('socios')->name('socios.')->group(function () {
        Route::get('/', [SocioController::class, 'index'])->name('index');
        Route::get('/{socio}', [SocioController::class, 'show'])->name('show');
        Route::patch('/{socio}', [SocioController::class, 'update'])->name('update');
        Route::get('/{socio}/documento/{tipo}', [SocioController::class, 'documento'])->name('documento');
});

require __DIR__.'/auth.php';
