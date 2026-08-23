<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarnetController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ConfiguracionController;
use App\Http\Controllers\Api\CuotaController;
use App\Http\Controllers\Api\CuotaTipoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentoController;
use App\Http\Controllers\Api\NoticiaController;
use App\Http\Controllers\Api\PersonaController;
use App\Http\Controllers\Api\SolicitudLicenciaController;
use App\Http\Controllers\Api\WebpayController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

// Verificación pública de carnet de socio (sin autenticación, para lectores QR).
Route::get('/verificar/{token}', [CarnetController::class, 'verificar']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/noticias', [NoticiaController::class, 'index']);
    Route::post('/noticias', [NoticiaController::class, 'store']);
    Route::get('/noticias/{noticia}', [NoticiaController::class, 'show']);
    Route::put('/noticias/{noticia}', [NoticiaController::class, 'update']);
    Route::delete('/noticias/{noticia}', [NoticiaController::class, 'destroy']);

    Route::get('/cuotas/mias', [CuotaController::class, 'mias']);
    Route::get('/cuotas', [CuotaController::class, 'index']);
    Route::post('/cuotas/asignar', [CuotaController::class, 'asignar']);
    Route::post('/cuotas/pagar-periodo-mensual', [CuotaController::class, 'pagarPeriodoMensual']);
    Route::post('/cuotas/{cuota}/pagar', [CuotaController::class, 'pagar']);
    Route::post('/cuotas/pagar-multiples', [CuotaController::class, 'pagarMultiples']);
    Route::post('/cuotas/{cuota}/aprobar', [CuotaController::class, 'aprobar']);
    Route::post('/cuotas/{cuota}/rechazar', [CuotaController::class, 'rechazar']);
    Route::post('/cuotas/aprobar-lote', [CuotaController::class, 'aprobarLote']);
    Route::post('/cuotas/enviar-recordatorios', [CuotaController::class, 'enviarRecordatorios']);

    Route::get('/cuota-tipos', [CuotaTipoController::class, 'index']);
    Route::post('/cuota-tipos', [CuotaTipoController::class, 'store']);
    Route::put('/cuota-tipos/{cuotaTipo}', [CuotaTipoController::class, 'update']);
    Route::delete('/cuota-tipos/{cuotaTipo}', [CuotaTipoController::class, 'destroy']);

    Route::get('/configuracion/cuota-mensual', [ConfiguracionController::class, 'showCuotaMensual']);
    Route::put('/configuracion/cuota-mensual', [ConfiguracionController::class, 'updateCuotaMensual']);
    Route::get('/configuracion/cuota-inscripcion', [ConfiguracionController::class, 'showCuotaInscripcion']);
    Route::put('/configuracion/cuota-inscripcion', [ConfiguracionController::class, 'updateCuotaInscripcion']);

    Route::get('/personas', [PersonaController::class, 'index']);
    Route::post('/personas', [PersonaController::class, 'store']);
    Route::get('/personas/{persona}', [PersonaController::class, 'show']);
    Route::put('/personas/{persona}', [PersonaController::class, 'update']);

    Route::get('/catalogos', [CatalogController::class, 'index']);

    Route::get('/documentos', [DocumentoController::class, 'index']);
    Route::post('/documentos', [DocumentoController::class, 'store']);
    Route::get('/documentos/{documento}', [DocumentoController::class, 'show']);
    Route::put('/documentos/{documento}', [DocumentoController::class, 'update']);
    Route::delete('/documentos/{documento}', [DocumentoController::class, 'destroy']);

    Route::get('/mi-carnet', [CarnetController::class, 'mio']);

    Route::get('/licencias/mias', [SolicitudLicenciaController::class, 'mias']);
    Route::post('/licencias', [SolicitudLicenciaController::class, 'store']);

    Route::post('/webpay/iniciar', [WebpayController::class, 'iniciar']);
});
