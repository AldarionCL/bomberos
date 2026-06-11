<?php

namespace App\Http\Controllers;

use App\Filament\Resources\PrecioCuotasResource;
use App\Models\Cuota;
use App\Models\CuotaTipo;
use App\Models\Documentos;
use App\Models\Persona;
use App\Models\PrecioCuotas;
use App\Models\User;
use App\Models\UserRole;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CuotasController extends Controller
{

    public function sincronizarCuotas($fechaInicio, $fechaFin, $actualizaMontos = false, $tipoCuotaParam = null)
    {

        // Si tipoCuotaParam es numericom, busca el nombre del tipo de cuota en la tabla CuotaTipo
        if (is_numeric($tipoCuotaParam)) {
            $tipoCuota = CuotaTipo::find($tipoCuotaParam);
            if ($tipoCuota) {
                $tipoCuotaParam = $tipoCuota->nombre;
            } else {
                $tipoCuotaParam = null; // Si no se encuentra, se asigna null
            }
        }

        // Trae las personas activas
        $personas = Persona::where('Activo', 1)
            ->orderBy('FechaReclutamiento', 'asc')
            ->get();

        foreach ($personas as $persona) {
//            $fechaReclutamiento = ($persona->FechaReclutamiento!='') ? Carbon::parse($persona->FechaReclutamiento) : Carbon::now()->firstOfYear();
            $tipoVoluntario = $persona->TipoVoluntario ?? 'miembro';
//            $antiguedad = $fechaReclutamiento->diffInYears(Carbon::now()) * -1;

            $exento = false;
//            if ($antiguedad >= 50) $exento = true;

            if (!$exento) {
                $tiposCuota = PrecioCuotas::where('TipoCuota', $tipoCuotaParam)
                    ->get();

                $fechaInicioProceso = Carbon::parse($fechaInicio)->firstOfMonth();
                $fechaFinProceso = Carbon::parse($fechaFin)->lastOfMonth();
                $diffMeses = round($fechaFinProceso->diffInMonths($fechaInicioProceso)) * -1;

                for ($i = 0; $i < $diffMeses; $i++) {

                    // calcula fecha periodo y vencimiento, agregando
                    $fechaPeriodo = $fechaInicioProceso->copy()->addMonths($i);
                    $fechaVencimiento = $fechaPeriodo->copy()->lastOfMonth();

                    // por cada tipo de cuota
                    foreach ($tiposCuota as $tipo) {
                        // si el tipo de cuota tiene monto asignado
                        if ($tipo->Monto > 0) {
                            $monto = $tipo->Monto;
                            $tipoCuota = $tipo->TipoCuota;

                            $existeCuota = Cuota::where('idUser', $persona->idUsuario)
                                ->where('FechaPeriodo', $fechaPeriodo->format('Y-m-01'))
                                ->where('TipoCuota', $tipoCuota)
                                ->exists();

                            if (!$existeCuota) {
                                $cuota = Cuota::Create(
                                    [
                                        'idUser' => $persona->idUsuario,
                                        'idCuotaTipo' => $tipoCuota->id,
                                        'FechaPeriodo' => $fechaPeriodo->format('Y-m-01'),
                                        'FechaVencimiento' => $fechaVencimiento->format('Y-m-d'),
                                        'Estado' => 1,
                                        'Monto' => $monto,
                                        'TipoCuota' => $tipoCuota,
                                        'Pendiente' => $monto,
                                        'Recaudado' => 0,
                                    ]);
                            } else {
                                if ($actualizaMontos) {
                                    // Actualiza monto en caso de que haya cambiado
                                    Cuota::where('idUser', $persona->idUsuario)
                                        ->where('FechaPeriodo', $fechaPeriodo->format('Y-m-01'))
                                        ->where('TipoCuota', $tipoCuota)
                                        ->where('Estado', 1)
                                        ->update(
                                            [
                                                'Monto' => $monto,
                                                'FechaVencimiento' => $fechaPeriodo->lastOfMonth()->format('Y-m-d'),
                                            ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    public
    function sincronizarUserPersona()
    {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->persona) {
                dump($user->persona);
            } else {
                $nuevo = Persona::create([
                    'idUsuario' => $user->id,
                    'idCargo' => 1,
                    'idEstado' => 1,
                    'FechaReclutamiento' => Carbon::today()->subDays(rand(0, 350))->format('Y-m-d'),
                    'Rut' => rand(11111111, 99999999) . "-" . rand(1, 9),
                    'Activo' => 1,
                ]);
                dump($nuevo);
            }
        }
    }

    public
    function revisaCuotasVencidas()
    {
        $cuotas = Cuota::where('Estado', 1)
            ->where('FechaVencimiento', '<', Carbon::now())
            ->get();

        foreach ($cuotas as $cuota) {
            $cuota->Estado = 0;
            $cuota->save();
        }
    }

    public
    static function exportResumen($idUsuario)
    {

        return Excel::download(new \App\Exports\ResumenCuotas($idUsuario), 'resumen-cuotas.xlsx');
    }


    public function pagarCuota($data, Cuota $record){

        $saldo = $data['MontoPagar'];
        $saldoFavor = Cuota::where('idUser', $record->idUser)
            ->where('SaldoFavor', '>', 0)
            ->first();

        $montoPagar = $record->Pendiente;
        $montoCuota = $record->Monto;
        $record->FechaPago = $data['FechaPago'];

        // uso del saldo a favor
        if ($saldoFavor) {
            if ($montoPagar >= $saldoFavor->SaldoFavor) {
                $montoPagar = $montoPagar - $saldoFavor->SaldoFavor;
                $saldoFavor->SaldoFavor = 0;
                $saldoFavor->save();

                Notification::make()
                    ->title('Saldo a Favor Aplicado')
                    ->body('Se ha aplicado un saldo a favor de $' . number_format($saldoFavor->SaldoFavor, 0, ',', '.'))
                    ->success()
                    ->icon('heroicon-s-check')
                    ->send();
            }
        }

        // el monto es suficiente para saldar la cuota por completo
        if ($montoPagar <= $saldo) {
            $record->Pendiente = 0;
            $record->Recaudado = $montoCuota;
            $saldo = $saldo - $montoPagar;

            $record->Estado = 5; // Estado 5, pendiente de aprobacion
            if(isset($data['checkAprobar']) && $data['checkAprobar']){
                $record->Estado = 2; // Estado 2, aprobado
                $record->AprobadoPor = Auth::user()->id;
            }

            Notification::make()
                ->title('Cuota Pagada')
                ->body('Se ha pagado la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                ->success()
                ->duration(5000)
                ->icon('heroicon-s-check')
                ->send();


            $documento = Documentos::create([
                'TipoDocumento' => 11, // Asumimos que es un comprobante de pago
                'Nombre' => $data['Documento'],
                'Path' => $data['DocumentoArchivo'],
                'Descripcion' => 'Comprobante de pago de cuota',
//                            'AsosiadoA' => Auth::user()->id,
            ]);

            $record->idDocumento = $documento->id;

            $record->save();

            if ($saldo > 0) {
                Notification::make()
                    ->title('Saldo a favor')
                    ->body('Se ha generado un saldo a favor de $' . number_format($saldo, 0, ',', '.'))
                    ->success()
                    ->icon('heroicon-s-check')
                    ->send();
                $record->update(['SaldoFavor' => $saldo]);
            }

            // emitir comprobante de cuotas con componente livewire.comprobante-cuota
            Notification::make()
                ->title('Comprobante generado')
                ->body('Haz clic para abrir el comprobante en una nueva pestaña.')
                ->success()
                ->icon('heroicon-s-document-text')
                ->actions([
                    \Filament\Notifications\Actions\Action::make('Abrir comprobante')
                        ->button()
                        ->url(route('comprobante-cuota', $documento->id), shouldOpenInNewTab: true),
                ])
                ->send()
                ->sendToDatabase($record->user);


        } else {

            Notification::make()
                ->title('El monto del pago es insuficiente')
                ->body('No se ha podido pagar la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y') . ', el monto ingresado es insuficiente.')
                ->danger()
                ->duration(5000)
                ->icon('heroicon-s-x-circle')
                ->send();

        }
    }

    public function pagarCuotas($data, $records){
        $saldo = $data['MontoPagar'];
        $saldoFavor = Cuota::where('idUser', $records->first()->idUser)
            ->where('SaldoFavor', '>', 0)
            ->first();

        $documento = Documentos::create([
            'TipoDocumento' => 11, // Asumimos que es un comprobante de pago
            'Nombre' => $data['Documento'],
            'Path' => $data['DocumentoArchivo'],
            'Descripcion' => 'Comprobante de pago de cuota',
//                            'AsosiadoA' => Auth::user()->id,
        ]);

        // Ordenar las cuotas seleccionadas por tipo y fecha de vencimiento
        /*$records = $records->sort(function ($a, $b) {
            if ($a->TipoCuota !== $b->TipoCuota) {
                return $a->TipoCuota === 'cuota_extraordinaria' ? 1 : -1;
            }
            return $a->fecha_vencimiento <=> $b->fecha_vencimiento;
        });*/

        $cuotaAnterior = null;

        foreach ($records as $record) {
            $montoPagar = $record->Pendiente;
            $montoCuota = $record->Monto;
            $record->FechaPago = $data['FechaPago'];

            // uso del saldo a favor
            if ($saldoFavor) {
                if ($montoPagar >= $saldoFavor->SaldoFavor) {
                    $montoPagar = $montoPagar - $saldoFavor->SaldoFavor;
                    $saldoFavor->SaldoFavor = 0;
                    $saldoFavor->save();

                    Notification::make()
                        ->title('Saldo a Favor Aplicado')
                        ->body('Se ha aplicado un saldo a favor de $' . number_format($saldoFavor->SaldoFavor, 0, ',', '.'))
                        ->success()
                        ->icon('heroicon-s-check')
                        ->send();
                }
            }

            // el monto es suficiente para saldar la cuota por completo
            if ($montoPagar <= $saldo) {
                $record->Pendiente = 0;
                $record->Recaudado = $montoCuota;
                $saldo = $saldo - $montoPagar;

                $record->idDocumento = $documento->id;
                $record->Estado = 5; // Estado 5, pendiente de aprobacion
                if(isset($data['checkAprobar']) && $data['checkAprobar']){
                    $record->Estado = 2; // Estado 2, aprobado
                    $record->AprobadoPor = Auth::user()->id;
                }

                Notification::make()
                    ->title('Cuota Pagada')
                    ->body('Se ha pagado la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                    ->success()
                    ->duration(5000)
                    ->icon('heroicon-s-check')
                    ->send();

                $record->save();
                $cuotaAnterior = $record;

            } else {
                if ($cuotaAnterior) {
                    $cuotaAnterior->SaldoFavor = $saldo;
                    $saldo = 0;
                    $cuotaAnterior->save();
                    Notification::make()
                        ->title('Saldo a favor')
                        ->body('Se ha generado un saldo a favor de $' . number_format($saldo, 0, ',', '.'))
                        ->success()
                        ->icon('heroicon-s-check')
                        ->send();
                }

                break;
            }

        }
        // Revisa si se genero al menos un pago
        if (Cuota::where('idDocumento', $documento->id)->count() == 0) {
            $documento->delete(); // limpia el documento si no se uso
        }

        // Revisa si queda saldo a favor para asignarlo a la ultima cuota pagada
        if ($saldo > 0) {
            Notification::make()
                ->title('Saldo a favor')
                ->body('Se ha generado un saldo a favor de $' . number_format($saldo, 0, ',', '.'))
                ->success()
                ->icon('heroicon-s-check')
                ->send();
            $records->last()->update(['SaldoFavor' => $saldo]);
        }

        // emitir comprobante de cuotas con componente livewire.comprobante-cuota
        Notification::make()
            ->title('Comprobante generado')
            ->body('Haz clic para abrir el comprobante en una nueva pestaña.')
            ->success()
            ->icon('heroicon-s-document-text')
            ->actions([
                \Filament\Notifications\Actions\Action::make('Abrir comprobante')
                    ->button()
                    ->url(route('comprobante-cuota', $documento->id), shouldOpenInNewTab: true),
            ])
            ->send()
            ->sendToDatabase($records[0]->user);
    }

    public function downloadPDF($idDocumento)
    {
        $records = Cuota::where('idDocumento', $idDocumento)->get();
        if ($records->isEmpty()) {
            abort(404, 'No se encontraron cuotas para este documento.');
        }

        $cuota = $records[0];
        $documento = $cuota->documento;
        $user = $cuota->user;
        $aprobador = $cuota->aprobador;

        if ($aprobador && $aprobador->name == 'Admin') {
            $tesorero = Persona::whereHas('cargo', fn($query) => $query->where('Cargo', 'Tesorero'))->first()?->user;
            if ($tesorero) {
                $aprobador = $tesorero;
            }
        }

        $logoPath = public_path('img/logo.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;

        $data = [
            'cuota' => $cuota,
            'records' => $records,
            'documento' => $documento,
            'user' => $user,
            'aprobador' => $aprobador,
            'logoBase64' => $logoBase64,
            'forPdf' => true,
        ];

        $pdf = Pdf::loadView('pdf.comprobante-cuota', $data);

        // Opciones para mejorar la renderización (ajustar según necesidad)
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Comprobante_' . ($documento->Nombre ?? $idDocumento) . '.pdf');
    }
}
