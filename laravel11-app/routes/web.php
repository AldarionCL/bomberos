<?php

use Filament\Actions\Exports\Http\Controllers\DownloadExport;
use Illuminate\Support\Facades\Route;

Route::get('/filament/exports/{export}/download', DownloadExport::class)
    ->name('filament.exports.download');

Route::get('/comprobante/{idDocumento}', \App\Livewire\ComprobanteCuota::class)
    ->name('comprobante-cuota');

Route::get('/descargar-comprobante/{idDocumento}', [\App\Http\Controllers\CuotasController::class, 'downloadPDF'])
    ->name('descargar-comprobante');

// Aplicación React (socios/tesorería/administración). El panel Filament sigue
// disponible en /app para tareas de backoffice no cubiertas todavía por la SPA.
$rutasReservadas = 'app|api|storage|sanctum|livewire|filament|up|build|vendor|img|css|js|comprobante|descargar-comprobante';

Route::get('/{any?}', function () {
    return view('spa');
})->where('any', '^(?!('.$rutasReservadas.')(/|$)).*$')->name('spa');
