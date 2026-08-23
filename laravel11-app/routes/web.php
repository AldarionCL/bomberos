<?php

use Filament\Actions\Exports\Http\Controllers\DownloadExport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/app');
});

Route::get('/filament/exports/{export}/download', DownloadExport::class)
    ->name('filament.exports.download');

Route::get('/comprobante/{idDocumento}', \App\Livewire\ComprobanteCuota::class)
    ->name('comprobante-cuota');

Route::get('/descargar-comprobante/{idDocumento}', [\App\Http\Controllers\CuotasController::class, 'downloadPDF'])
    ->name('descargar-comprobante');

// Retorno de Webpay Plus (Transbank redirige el navegador aquí, sin sesión).
Route::match(['get', 'post'], '/webpay/retorno', [\App\Http\Controllers\Api\WebpayController::class, 'retorno'])
    ->name('webpay.retorno');

// Aplicación React (socios/tesorería/administración), aislada bajo /v2. El
// panel Filament sigue disponible en /app para tareas de backoffice no
// cubiertas todavía por la SPA.
Route::get('/v2/{any?}', function () {
    return view('spa');
})->where('any', '.*')->name('spa');
