<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Recordatorio semanal a socios con cuotas vencidas. Requiere que el servidor
// tenga configurado el cron de Laravel (`* * * * * php artisan schedule:run`).
Schedule::command('cuotas:recordatorios')->weeklyOn(1, '08:00');
