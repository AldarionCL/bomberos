<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Documentos;
use App\Models\Persona;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class CuotasController extends Controller
{
    public
    static function exportResumen($idUsuario)
    {

        return Excel::download(new \App\Exports\ResumenCuotas($idUsuario), 'resumen-cuotas.xlsx');
    }


    public function pagarCuota($data, Cuota $record){

        $saldo = $data['MontoPagar'];
        $saldoFavor = Cuota::where('idUser', $record->idUser)
            ->where('SaldoFavor', '>', 0)
            ->whereHas('estadocuota', function ($query) {
                $query->whereIn('Estado', ['Aprobado', 'Pagada']);
            })
            ->first();

        $montoPagar = $record->Pendiente;
        $montoCuota = $record->Monto;
        $record->FechaPago = $data['FechaPago'];

        // uso del saldo a favor
        if ($saldoFavor) {
            $saldoFavorAplicado = $saldoFavor->SaldoFavor;

            if ($montoPagar >= $saldoFavor->SaldoFavor) {
                $montoPagar = $montoPagar - $saldoFavor->SaldoFavor;
                $saldoFavor->SaldoFavor = 0;
                $saldoFavor->save();

                Notification::make()
                    ->title('Saldo a Favor Aplicado')
                    ->body('Se ha aplicado un saldo a favor de $' . number_format($saldoFavorAplicado, 0, ',', '.'))
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
                'TipoDocumento' => 11,
                'Nombre' => '',
                'NroDocumento' => $data['NroDocumento'],
                'Path' => $data['DocumentoArchivo'],
                'Descripcion' => 'Comprobante de pago de cuota',
            ]);
            $documento->update(['Nombre' => str_pad($documento->id, 6, '0', STR_PAD_LEFT)]);

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

            // Notificar a tesoreros y administradores (excluyendo al usuario que registra el pago)
            $notifyAdmins = User::whereHas('role', fn($q) => $q->where('Rol', 'Administrador'))
                ->orWhereHas('persona.cargo', fn($q) => $q->where('Cargo', 'Tesorero'))
                ->where('id', '!=', Auth::id())
                ->get();

            if ($notifyAdmins->isNotEmpty()) {
                Notification::make()
                    ->title('Nuevo pago registrado')
                    ->body(($record->user->name ?? 'Un usuario') . ' registró un pago de $' . number_format($montoCuota, 0, ',', '.') . ' — periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                    ->info()
                    ->icon('heroicon-s-currency-dollar')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('Ver comprobante')
                            ->button()
                            ->url(route('comprobante-cuota', $documento->id), shouldOpenInNewTab: true),
                    ])
                    ->sendToDatabase($notifyAdmins);
            }

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
            ->whereHas('estadocuota', function ($query) {
                $query->whereIn('Estado', ['Aprobado', 'Pagada']);
            })
            ->first();

        $documento = Documentos::create([
            'TipoDocumento' => 11,
            'Nombre' => '',
            'NroDocumento' => $data['NroDocumento'],
            'Path' => $data['DocumentoArchivo'],
            'Descripcion' => 'Comprobante de pago de cuota',
        ]);
        $documento->update(['Nombre' => str_pad($documento->id, 6, '0', STR_PAD_LEFT)]);

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
                $saldoFavorAplicado = $saldoFavor->SaldoFavor;

                if ($montoPagar >= $saldoFavor->SaldoFavor) {
                    $montoPagar = $montoPagar - $saldoFavor->SaldoFavor;
                    $saldoFavor->SaldoFavor = 0;
                    $saldoFavor->save();

                    Notification::make()
                        ->title('Saldo a Favor Aplicado')
                        ->body('Se ha aplicado un saldo a favor de $' . number_format($saldoFavorAplicado, 0, ',', '.'))
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

        // Notificar a tesoreros y administradores (excluyendo al usuario que registra el pago)
        $totalPagadas = Cuota::where('idDocumento', $documento->id)->count();
        if ($totalPagadas > 0) {
            $notifyAdmins = User::whereHas('role', fn($q) => $q->where('Rol', 'Administrador'))
                ->orWhereHas('persona.cargo', fn($q) => $q->where('Cargo', 'Tesorero'))
                ->where('id', '!=', Auth::id())
                ->get();

            if ($notifyAdmins->isNotEmpty()) {
                Notification::make()
                    ->title('Nuevo pago registrado')
                    ->body(($records[0]->user->name ?? 'Un usuario') . ' registró ' . $totalPagadas . ' pago(s) por $' . number_format($data['MontoPagar'], 0, ',', '.'))
                    ->info()
                    ->icon('heroicon-s-currency-dollar')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('Ver comprobante')
                            ->button()
                            ->url(route('comprobante-cuota', $documento->id), shouldOpenInNewTab: true),
                    ])
                    ->sendToDatabase($notifyAdmins);
            }
        }
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
        $aprobadorNombre = $aprobador ? 'Directiva2026' : null;

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
            'aprobadorNombre' => $aprobadorNombre,
            'logoBase64' => $logoBase64,
            'forPdf' => true,
        ];

        $pdf = Pdf::loadView('pdf.comprobante-cuota', $data);

        // Opciones para mejorar la renderización (ajustar según necesidad)
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Comprobante_' . ($documento->Nombre ?? $idDocumento) . '.pdf');
    }
}
