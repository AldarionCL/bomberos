<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
//    return redirect('/app');
    return view('welcome');
});

Route::get('/sincCuotas', [\App\Http\Controllers\CuotasController::class, 'sincronizarCuotas'] );
Route::get('/sincPersonas', [\App\Http\Controllers\CuotasController::class, 'sincronizarUserPersona'] );
