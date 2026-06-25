<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use App\Filament\Pages\ComprobantePago;
use App\Filament\Resources\CuotasPersonaResource;
use App\Http\Controllers\CuotasController;
use App\Livewire\ComprobanteCuota;
use App\Models\Cuota;
use App\Models\Documentos;
use App\Models\DocumentosCuotas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Torgodly\Html2Media\Tables\Actions\Html2MediaAction;

class CuotasRelationManager extends RelationManager
{
    protected static string $relationship = 'cuotas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('comprobante')
                        ->label('Ver Recibo de Pago')
                        ->url(fn($record) => route('comprobante-cuota', $record->idDocumento))
                        ->openUrlInNewTab()
                        ->icon('heroicon-s-document-text')
                        ->color('success')
                        ->visible(fn($record) => $record->idDocumento)
                        ->size('md'),
                ]),
                Forms\Components\Section::make('Informacion Cuota')
                    ->schema([
                        Forms\Components\Placeholder::make('FechaPeriodo')
                            ->content(fn($record) => Carbon::parse($record->FechaPeriodo)->format('d/m/Y')),

                        Forms\Components\Placeholder::make('FechaVencimiento')
                            ->label('Fecha de Vencimiento')
                            ->content(fn($record) => Carbon::parse($record->FechaVencimiento)->format('d/m/Y')),

                        Select::make('idCuotaTipo')
                            ->relationship('tipo', 'nombre')
                            ->label('Tipo de Cuota (Nuevo)')
                            ->placeholder('Seleccione un tipo'),

                        Select::make('Estado')
                            ->relationship('estadocuota', 'Estado')
                            ->default(1)
                            ->disabled(fn($record) => !Auth::user()->isRole('Administrador'))
                            ->required()
                            ->label('Estado'),

                        Forms\Components\Placeholder::make('Monto')
                            ->label('Monto de cuota')
                            ->content(fn($record) => "$" . number_format($record->Monto, 0, ',', '.')),
                        Forms\Components\Placeholder::make('Pendiente')
                            ->label('Monto Pendiente')
                            ->content(fn($record) => "$" . number_format($record->Pendiente, 0, ',', '.')),
                        Forms\Components\Placeholder::make('Recaudado')
                            ->label('Monto Recaudado')
                            ->content(fn($record) => "$" . number_format($record->Recaudado, 0, ',', '.')),
                        Forms\Components\Placeholder::make('SaldoFavor')
                            ->label('Saldo a Favor')
                            ->content(fn($record) => "$" . number_format($record->SaldoFavor, 0, ',', '.')),
                        Forms\Components\Placeholder::make('TipoCuota')
                            ->label('Tipo de Cuota')
                            ->content(fn($record) => $record->TipoCuota == 'cuota_ordinaria' ? 'Cuota Ordinaria' : 'Cuota Extraordinaria'),
                        Forms\Components\Placeholder::make('AprobadoPor')
                            ->label('Aprobado Por')
                            ->content(fn($record) => $record->aprobador ? $record->aprobador->name : 'Sin aprobacion'),

                        Forms\Components\Placeholder::make('MotivoRechazo')
                            ->label('Motivo de Rechazo')
                            ->content(fn($record) => $record->MotivoRechazo ?? '-')
                            ->visible(fn($record) => $record && $record->estadocuota?->Estado === 'Rechazado')
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Comprobantes de Pago')
                    ->relationship('documento')
                    ->schema([
                        Forms\Components\Placeholder::make('Ndocumento')
                            ->label('N° Documento')
                            ->content(fn($record) => $record->Nombre ?? 'No asignado'),
                        Forms\Components\Placeholder::make('FechaPago')
                            ->label('Fecha de Pago')
                            ->content(fn($record) => isset($record->FechaPago) ? Carbon::parse($record->FechaPago)->format('d/m/Y') : 'No asignado'),
                        Forms\Components\FileUpload::make('Path')
                            ->label('Archivo Comprobante')
                            ->required()
                            ->disk('public')
                            ->directory('comprobantesCuotas')
                            ->deletable(false)
                            ->previewable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Cuotas')
            ->columns([
                Tables\Columns\TextColumn::make('TipoCuota')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cuota_ordinaria' => 'Ordinaria',
                        'cuota_extraordinaria' => 'Extraordinaria',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('FechaPeriodo')
                    ->label('Periodo')
                    ->date("d/m/Y")
                    ->sortable(),

                Tables\Columns\TextColumn::make('FechaVencimiento')
                    ->label('Vencimiento')
                    ->date("d/m/Y"),

                Tables\Columns\TextColumn::make('Pendiente')
                    ->money('CLP', locale: 'es_CL')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('Recaudado')
                    ->money('CLP', locale: 'es_CL')
                    ->color('success'),

                Tables\Columns\TextColumn::make('estadocuota.Estado')
                    ->label('Estado cuota')
                    ->badge()
                    ->grow(false)
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'badgeAlert',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Cancelado' => 'danger',
                        'Pendiente Aprobacion' => 'warning',
                        default => 'gray',
                    })
                    ->tooltip(fn($record) => $record->estadocuota?->Estado === 'Rechazado' && $record->MotivoRechazo
                        ? 'Motivo: ' . $record->MotivoRechazo
                        : null)
                    ->visibleFrom('md'),

                Tables\Columns\TextColumn::make('documento.Nombre')
                    ->label('Comprobante')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-s-document-text')
                    ->url(fn($record) => $record->documento ? asset('storage/' . $record->documento->Path) : null, true),


                Tables\Columns\TextColumn::make('FechaPago')
                    ->date("d/m/Y")
                    ->visibleFrom('md'),
            ])
            ->defaultSort(fn($query) => $query->orderBy('FechaPeriodo', 'asc')->orderBy('TipoCuota', 'desc'))
            ->filters([
                // filtro por fecha de vencimiento de la cuota con select filter
                Tables\Filters\Filter::make('FechaVencimiento')
                    ->form([
                        Forms\Components\DatePicker::make('fecha')
                            ->label('Fecha de Vencimiento'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['fecha'], fn($query, $fecha) => $query->whereDate('FechaVencimiento', '>=', $fecha)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['fecha']) {
                            return null;
                        }

                        return 'Fecha Vencimiento desde : ' . Carbon::parse($data['fecha'])->format('d/m/Y');
                    }),
            ])
//            ->groups(['documentos.id'])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('Pagar')
                    ->button()
                    ->icon('heroicon-s-currency-dollar')
                    ->label('Pagar cuota')
                    ->form(function ($record) {
                        $saldoFavor = Cuota::where('idUser', $record->idUser)
                            ->where('SaldoFavor', '>', 0)
                            ->sum('SaldoFavor');

                        $montoPendiente = $record->Pendiente;
                        $montoTotalPendiente = $montoPendiente - $saldoFavor;

                        return [
                            Forms\Components\Placeholder::make('')
                                ->content('Se va a pagar una cuota con un monto total pendiente de $' . number_format($montoPendiente, 0, ',', '.')),
                            Forms\Components\Placeholder::make('')
                                ->content('Usted posee un saldo a favor de $' . number_format($saldoFavor, 0, ',', '.')
                                    . ' por lo que el monto a pagar es de $' . number_format($montoTotalPendiente, 0, ',', '.'))
                                ->visible($saldoFavor > 0),

                            Section::make()
                                ->schema([
                                    TextInput::make('MontoPendiente')
                                        ->label('Monto Pendiente')
                                        ->prefix('$')
                                        ->default(fn($record) => $montoTotalPendiente)
                                        ->disabled()
                                        ->suffixAction(Forms\Components\Actions\Action::make('aplicar')
                                            ->icon('heroicon-m-arrow-right-circle')
                                            ->label('Aplicar Monto Pendiente')
                                            ->action(function ($record, $set) use ($montoTotalPendiente) {
                                                $set('MontoPagar', $montoTotalPendiente);
                                            })
                                        ),
                                    TextInput::make('MontoPagar')
                                        ->label('Monto a Pagar')
                                        ->prefix('$')
                                        ->required()
                                        ->live(onBlur: true)
                                ])->columns(),
                            Section::make('Documentos')
                                ->schema([
                                    Flatpickr::make('FechaPago')->label('Fecha de Pago')
                                        ->default(fn() => Carbon::today()->format('Y-m-d'))
                                        ->required(),
                                    Forms\Components\FileUpload::make('DocumentoArchivo')
                                        ->label('Archivo Comprobante')
                                        ->required()
                                        ->disk('public')
                                        ->directory('comprobantesCuotas')
                                        ->deletable(false)
                                        ->previewable()
                                        ->downloadable()
                                        ->columnSpanFull()
                                ])->columns(),


                            Forms\Components\Checkbox::make('checkAprobar')
                                ->label('Marcar cuota como aprobada automáticamente (solo Admin y Tesorero)')
                                ->helperText('Si se selecciona esta opción, la cuota pagada se marcará automáticamente como aprobada.')
                                ->default(false)
                                ->visible(fn() => Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')),

                        ];
                    })
                    ->action(function (array $data, $record) {

                        $controlador = new CuotasController();
                        $controlador->pagarCuota($data, $record);

                        return redirect(request()->header('Referer'));

                    })
                    ->visible(fn($record) => $record->Estado == 1 && $record->Pendiente > 0),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->button()
                    ->color('info')
                    ->disabled(
                        function ($record) {
                            if (Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')) {
                                return false;
                            } else {
                                if ($record->Estado == 2) {
                                    return true;
                                } else {
                                    return false;
                                }
                            }
                        }
                    ),

                Tables\Actions\Action::make('AprobarPago')
                    ->label('Aprobar')
                    ->action(function ($record) {
                        $idDocumento = $record->idDocumento;
//                        $record->update(['Estado' => 2, 'AprobadoPor' => Auth::user()->id]);
                        $cuotas = Cuota::where('idDocumento', $idDocumento)
                            ->where('Estado', 5)
                            ->get();

                        Cuota::where('idDocumento', $idDocumento)
                            ->where('Estado', 5)
                            ->update(['Estado' => 2, 'AprobadoPor' => Auth::user()->id]);

                        Notification::make()
                            ->title('Pago Aprobado')
                            ->success()
                            ->icon('heroicon-s-check')
                            ->send();

                        foreach ($cuotas as $cuota) {
                            Notification::make()
                                ->title('Pago Aprobado')
                                ->body('Se ha aprobado el pago de la cuota del periodo ' . Carbon::parse($cuota->FechaPeriodo)->format('d/m/Y'))
                                ->success()
                                ->icon('heroicon-s-check')
                                ->sendToDatabase($cuota->user);
                        }

                    })
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->disabled(fn($record) => $record->Pendiente > 0)
                    ->visible(fn($record) => (Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')) && $record->Estado == 5)
                    ->requiresConfirmation(),

                Tables\Actions\Action::make('RechazarPago')
                    ->label('Rechazar')
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-s-x-circle')
                    ->visible(fn($record) => (Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')) && $record->Estado == 5)
                    ->form([
                        Forms\Components\Textarea::make('MotivoRechazo')
                            ->label('Motivo de rechazo')
                            ->required()
                            ->rows(3)
                            ->placeholder('Indique el motivo por el cual se rechaza este pago...'),
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'Estado' => 3,
                            'MotivoRechazo' => $data['MotivoRechazo'],
                        ]);

                        Notification::make()
                            ->title('Pago Rechazado')
                            ->danger()
                            ->icon('heroicon-s-x-circle')
                            ->send();

                        Notification::make()
                            ->title('Pago Rechazado')
                            ->body('Se ha rechazado el pago de tu cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y') . '. Motivo: ' . $data['MotivoRechazo'])
                            ->danger()
                            ->icon('heroicon-s-x-circle')
                            ->sendToDatabase($record->user);
                    })
                    ->modalHeading('Rechazar pago')
                    ->modalDescription('Esta acción marcará la cuota como rechazada y notificará al voluntario.')
                    ->requiresConfirmation(false),

                Tables\Actions\Action::make('GenerarComprobante')
                    ->label('Generar Comprobante')
                    ->button()
                    ->color('warning')
                    ->icon('heroicon-s-document-plus')
                    ->visible(fn($record) => $record->Estado == 2 && !$record->idDocumento)
                    ->modalHeading('Generar comprobante de pago')
                    ->modalDescription('Esta cuota fue aprobada sin un comprobante adjunto. Complete los datos para generarlo.')
                    ->form(fn($record) => [
                        Flatpickr::make('FechaPago')
                            ->label('Fecha de Pago')
                            ->default(fn() => $record->FechaPago
                                ? Carbon::parse($record->FechaPago)->format('Y-m-d')
                                : Carbon::today()->format('Y-m-d'))
                            ->required(),
                        Forms\Components\FileUpload::make('DocumentoArchivo')
                            ->label('Archivo Comprobante (opcional)')
                            ->disk('public')
                            ->directory('comprobantesCuotas')
                            ->deletable(false)
                            ->previewable()
                            ->downloadable()
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, $record) {
                        $documento = Documentos::create([
                            'TipoDocumento' => 11,
                            'Nombre' => '',
                            'Path' => $data['DocumentoArchivo'] ?? null,
                            'Descripcion' => 'Comprobante de pago de cuota',
                        ]);
                        $documento->update(['Nombre' => str_pad($documento->id, 6, '0', STR_PAD_LEFT)]);

                        $record->update([
                            'idDocumento' => $documento->id,
                            'FechaPago' => $data['FechaPago'],
                        ]);

                        Notification::make()
                            ->title('Comprobante generado')
                            ->body('Se ha generado el comprobante de pago correctamente.')
                            ->success()
                            ->icon('heroicon-s-document-text')
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('Abrir comprobante')
                                    ->button()
                                    ->url(route('comprobante-cuota', $documento->id), shouldOpenInNewTab: true),
                            ])
                            ->send();
                    }),

                Tables\Actions\Action::make('VerComprobante')
                    ->label('Recibo de Pago')
                    ->url(fn($record) => route('comprobante-cuota', $record->idDocumento))
                    ->openUrlInNewTab()
                    ->button()
                    ->visible(fn($record) => $record->Estado == 2 && $record->idDocumento)
                    ->color('success')
                    ->icon('heroicon-s-document-text'),

                Tables\Actions\Action::make('DescargarPDF')
                    ->label('Descargar PDF')
                    ->url(fn($record) => route('descargar-comprobante', $record->idDocumento))
                    ->openUrlInNewTab()
                    ->button()
                    ->visible(fn($record) => $record->Estado == 2 && $record->idDocumento)
                    ->color('primary')
                    ->icon('heroicon-o-arrow-down-tray'),

                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color('info'),


                /*Tables\Actions\Action::make('pdf')
                    ->action(function ($record) {
                        $pdf = Pdf::loadView('livewire.comprobante-cuota', ['record' => $record->id]);
//                        dd($pdf);
                        $pdf->setOption(['dpi' => 150,
                            'defaultFont' => 'DejaVu Sans',
                            'isPhpEnabled' => true,
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true]);
//                        return $pdf->download('invoice.pdf');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'ComprobanteCuota.pdf');
                    })
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-s-document-text')
                    ->visible(fn($record) => $record->Estado == 2)
                    ->openUrlInNewTab(),*/


            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('AprobarMasivo')
                    ->label('Pagar cuota(s) seleccionada(s)')
                    ->form(function ($records) {
                        $saldoFavor = Cuota::where('idUser', $records->first()->idUser)
                            ->where('SaldoFavor', '>', 0)
                            ->sum('SaldoFavor');
                        $saldoFavorRestado = $saldoFavor;

                        $montoPendiente = $records->sum('Pendiente');
                        $montoTotalPendiente = $montoPendiente - $saldoFavor;
                        if($montoTotalPendiente < 0) {
                            $montoTotalPendiente = 0;
                            $saldoFavorRestado = $saldoFavor - $montoPendiente;
                        }

                        return [
                            Forms\Components\Placeholder::make('')
                                ->content('Se van a pagar ' . $records->count() . ' cuota(s) con un monto total pendiente de $' . number_format($montoPendiente, 0, ',', '.')),
                            Forms\Components\Placeholder::make('')
                                ->content('Usted posee un saldo a favor de $' . number_format($saldoFavor, 0, ',', '.')
                                    . ' por lo que el monto a pagar es de $' . number_format($montoTotalPendiente, 0, ',', '.'))
                                ->visible($saldoFavor > 0),

                            Section::make()
                                ->schema([
                                    TextInput::make('MontoPendiente')
                                        ->label('Monto a Pagar de cuota(s)')
                                        ->prefix('$')
                                        ->default(fn($record) => $montoPendiente)
                                        ->disabled()
                                        ->suffixAction(Forms\Components\Actions\Action::make('aplicar')
                                            ->icon('heroicon-m-arrow-right-circle')
                                            ->label('Aplicar Monto Pendiente')
                                            ->action(function ($record, $set) use ($montoPendiente) {
                                                $set('MontoPagar', $montoPendiente);
                                            })
                                        ),
                                    TextInput::make('MontoPagar')
                                        ->label('Monto a Pagar')
                                        ->prefix('$')
                                        ->default(0)
                                        ->required()
                                        ->live(onBlur: true)
                                ])->columns(),
                            Section::make('Documentos')
                                ->schema([
                                    Flatpickr::make('FechaPago')->label('Fecha de Pago')
                                        ->default(fn() => Carbon::today()->format('Y-m-d'))
                                        ->required(),
                                    Forms\Components\FileUpload::make('DocumentoArchivo')
                                        ->label('Archivo Comprobante')
                                        ->required()
                                        ->disk('public')
                                        ->directory('comprobantesCuotas')
                                        ->deletable(false)
                                        ->previewable()
                                        ->downloadable()
                                        ->columnSpanFull()
                                ])->columns(),

                            Forms\Components\Checkbox::make('checkAprobar')
                                ->label('Marcar cuotas como aprobadas automáticamente (solo Admin y Tesorero)')
                                ->helperText('Si se selecciona esta opción, las cuotas pagadas se marcarán automáticamente como aprobadas.')
                                ->default(false)
                                ->visible(fn() => Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')),

                            Forms\Components\Placeholder::make('')
                                ->content('* El sistema aplicará el monto ingresado a las cuotas seleccionadas. Si el monto ingresado excede el total pendiente, se generará un saldo a favor en la ultima cuota saldada. '),

                        ];
                    })
                    ->action(function (array $data, $records) {

                        $controlador = new CuotasController();
                        $controlador->pagarCuotas($data, $records);

                        return redirect(request()->header('Referer'));

                    })
                    ->deselectRecordsAfterCompletion(),
            ])->checkIfRecordIsSelectableUsing(fn($record) => $record->Estado == 1 && $record->Pendiente > 0);
    }

    protected function afterSave(): void
    {
        if ($this->record->Recaudado == $this->record->Monto) {
            Notification::make()
                ->title('Cuota  Pagada')
                ->body('Se ha pagado la cuota del periodo ' . Carbon::parse($this->record->FechaPeriodo)->format('d/m/Y'))
                ->success()
                ->icon('heroicon-s-check')
                ->send();
        }

        Notification::make()
            ->title('Cuota Actualizada')
            ->body('Se ha actualizado la cuota del periodo ' . Carbon::parse($this->ownerRecord->FechaPeriodo)->format('d/m/Y'))
            ->success()
            ->icon('heroicon-s-check')
            ->sendToDatabase($this->ownerRecord->user);
    }

    public function getTabs(): array
    {
        return [
            'Cuotas Pendientes' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('Estado', [1, 5])),
            'Cuotas Aprobadas' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Estado', 2)),
            'Todas' => Tab::make(),

        ];
    }

}
