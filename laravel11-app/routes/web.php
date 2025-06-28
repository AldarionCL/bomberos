<?php

use Filament\Actions\Exports\Http\Controllers\DownloadExport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
//    return view('welcome');
});

Route::get('/sincCuotas', [\App\Http\Controllers\CuotasController::class, 'sincronizarCuotas'] );
Route::get('/sincPersonas', [\App\Http\Controllers\CuotasController::class, 'sincronizarUserPersona'] );

Route::get('/filament/exports/{export}/download', DownloadExport::class)
    ->name('filament.exports.download');

Route::get('/comprobante/{record}', \App\Livewire\ComprobanteCuota::class)
    ->name('comprobante-cuota');
