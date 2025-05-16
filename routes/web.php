<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraEntradaController;


Route::get('/', function () {
    return view('welcome');
});

// Rutas para comprar entradas
Route::get('/comprar/{evento}', [CompraEntradaController::class, 'show'])->name('comprar.entrada');
Route::post('/comprar/{evento}', [CompraEntradaController::class, 'store'])->name('comprar.entrada.procesar');

// Ruta para ver entradas vendidas
Route::get('/tickets', [CompraEntradaController::class, 'index'])->name('tickets.index');


// Mostrar formulario de compra
Route::get('/evento/{evento}/comprar', [CompraEntradaController::class, 'show'])
    ->name('comprar.entrada');

// Procesar compra
Route::post('/evento/{evento}/comprar', [CompraEntradaController::class, 'store'])
    ->name('comprar.entrada.store');