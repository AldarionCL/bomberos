<?php

namespace App\Console\Commands;

use App\Services\CuotaMensualService;
use Illuminate\Console\Command;

class EnviarRecordatoriosCuotas extends Command
{
    protected $signature = 'cuotas:recordatorios';

    protected $description = 'Envía un recordatorio (email + notificación) a los socios con cuotas vencidas sin pagar';

    public function handle(CuotaMensualService $cuotaMensualService): int
    {
        $atrasados = $cuotaMensualService->personasConAtraso();

        $this->info("Socios con cuotas vencidas: {$atrasados->count()}");

        foreach ($atrasados as $atraso) {
            $cuotaMensualService->enviarRecordatorio($atraso);
            $this->line('- Recordatorio enviado a: '.($atraso['persona']->user?->name ?? $atraso['persona']->idUsuario));
        }

        $this->info('Listo.');

        return self::SUCCESS;
    }
}
