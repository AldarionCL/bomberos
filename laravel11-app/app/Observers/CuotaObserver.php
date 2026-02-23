<?php

namespace App\Observers;

use App\Models\Cuota;
use App\Models\Caja;
use App\Models\CuotasEstados;
use Illuminate\Support\Facades\Auth;

class CuotaObserver
{
    /**
     * Handle the Cuota "updated" event.
     */
    public function updated(Cuota $cuota): void
    {
        // Si el estado ha cambiado
        if ($cuota->isDirty('Estado')) {
            $nuevoEstado = CuotasEstados::find($cuota->Estado);

            // Si el nuevo estado es "Pagada" o similar (asumiendo que hay un estado que significa aprobado/pagado)
            // Según el código anterior en CuotasPendientesResource, los pendientes son 'Pendiente' y 'Pendiente Aprobacion'.
            // Necesito saber cuál es el estado de "Pagada".

            if ($nuevoEstado && ($nuevoEstado->Estado === 'Pagada' || $nuevoEstado->Estado === 'Aprobado')) {
                Caja::create([
                    'monto' => $cuota->Monto,
                    'impuesto' => 0,
                    'total' => $cuota->Monto,
                    'id_usuario' => Auth::id(),
                    'descripcion' => "Pago de cuota - Usuario ID: {$cuota->idUser} - Periodo: {$cuota->FechaPeriodo}",
                    'tipo' => 'Ingreso cuota',
                ]);
            }
        }
    }
}
