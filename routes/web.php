<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

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

require __DIR__.'/auth.php';
