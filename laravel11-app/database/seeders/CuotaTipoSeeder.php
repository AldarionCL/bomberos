<?php

namespace Database\Seeders;

use App\Models\CuotaTipo;
use Illuminate\Database\Seeder;

class CuotaTipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cuotas = [
            'Cuota Inscripción Club',
            'Cuota Mensual',
            'Cuota Asado Inicio Año',
            'Cuota Aniversario Socio',
            'Cuota Aniversario Acompañante',
            'Cuota Asado Pos Maraton',
            'Cuota sesión masaje',
            'Cuota Acompañante Tallarinata',
            'Cuota Maratón Maule',
            'Cuota Maratón de Santiago',
            'Cuota Maratón Viña del Mar',
            'Cuota Polera Florida Runners',
            'Cuota Cortaviento Florida Runners',
            'Cuota Buzos Florida Runners',
        ];

        foreach ($cuotas as $nombre) {
            CuotaTipo::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'tipoCobro' => 'CUOTA',
                    'activo' => true,
                ]
            );
        }

        $pagos = [
            'Pago insumos tallarinata',
            'Pago fiesta aniversario',
            'Pago Regalo día de la Madre',
            'Pago regalo día del Padre',
            'Pago Clases Kineadvance Limitada',
            'Pago Clases Profesor Running',
            'Pago Arriendo Espacios Entrenamiento',
            'Pago Regalos Fiesta Aniversario',
            'Pago fiesta 18 de septiembre',
            'Pago Ocasiones especial',
            'Pago Maratón Maule',
            'Pago Maratón de Santiago',
            'Pago Maratón Viña del Mar',
        ];

        foreach ($pagos as $nombre) {
            CuotaTipo::updateOrCreate(
                ['nombre' => $nombre],
                [
                    'tipoCobro' => 'PAGO',
                    'activo' => true,
                ]
            );
        }
    }
}
