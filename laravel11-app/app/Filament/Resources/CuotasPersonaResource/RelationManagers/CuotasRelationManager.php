<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use App\Filament\Pages\ComprobantePago;
use App\Filament\Resources\CuotasPersonaResource;
use App\Livewire\ComprobanteCuota;
use App\Models\Cuota;
use App\Models\Documentos;
use App\Models\DocumentosCuotas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Actions\Action;
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
                Forms\Components\Section::make('Informacion Cuota')
                    ->schema([
//                        Forms\Components\Placeholder::make('Monto')
//                            ->content(fn($record) => "$" . $record->Monto),

//                    DatePicker::make('fechaPeriodo')->label('Fecha de Periodo'),
                        Forms\Components\Placeholder::make('FechaPeriodo')
                            ->content(fn($record) => Carbon::parse($record->FechaPeriodo)->format('d/m/Y')),

                        Forms\Components\Placeholder::make('FechaVencimiento')
                            ->label('Fecha de Vencimiento')
                            ->content(fn($record) => Carbon::parse($record->FechaVencimiento)->format('d/m/Y')),

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
                    ])->columns(3),

                Section::make('Comprobantes')
                    ->schema([
                        Forms\Components\Repeater::make('Comprobante')
                            ->relationship('documentos')
                            ->label('')
                            ->schema([
                                Forms\Components\Placeholder::make('Ndocumento')
                                    ->label('N° Documento')
                                    ->content(fn($record) => $record->Nombre),
                                Forms\Components\Placeholder::make('FechaPago')
                                    ->label('Fecha de Pago')
                                    ->content(fn($record) => Carbon::parse($record->FechaPago)->format('d/m/Y')),
                                Forms\Components\FileUpload::make('Path')
                                    ->label('Archivo Comprobante')
                                    ->required()
                                    ->disk('public')
                                    ->directory('comprobantesCuotas')
                                    ->deletable(false)
                                    ->previewable()
                                    ->downloadable()
                                    ->columnSpanFull(),
                            ])->columns()
                            ->grid(),
                    ]),
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
                    ->date("d/m/Y"),

                Tables\Columns\TextColumn::make('FechaVencimiento')
                    ->date("d/m/Y"),

                Tables\Columns\TextColumn::make('Pendiente')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('Recaudado')
                    ->color('success'),

                Tables\Columns\TextColumn::make('estadocuota.Estado')
                    ->badge()
                    ->grow(false)
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'badgeAlert',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Cancelado' => 'danger',
                        default => 'gray',
                    })->visibleFrom('md'),

                Tables\Columns\TextColumn::make('FechaPago')
                    ->date("d/m/Y")
                    ->visibleFrom('md'),
            ])
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
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ingresar Pago')
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
                Tables\Actions\ViewAction::make()
                    ->button()
                    ->color('info'),
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('AprobarPago')
                    ->action(function ($record) {
                        $record->update(['Estado' => 2]);
                        $record->update(['AprobadoPor' => Auth::user()->id]);

                        Notification::make()
                            ->title('Pago Aprobado')
                            ->success()
                            ->icon('heroicon-s-check')
                            ->send();

                        Notification::make()
                            ->title('Pago Aprobado')
                            ->body('Se ha aprobado el pago de la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                            ->success()
                            ->icon('heroicon-s-check')
                            ->sendToDatabase($record->user);
                    })
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->visible(fn($record) => $record->Estado != 2)
                    ->disabled(function ($record) {
                        if (Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero')) {
                            if (($record->Recaudado == $record->Monto)) {
                                return false;
                            } else {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    })
                    ->requiresConfirmation(),

                /*                Tables\Actions\Action::make('comprobante')
                                    ->url(fn($record) => route(ComprobantePago::getRouteName()))
                                    ->button()
                                    ->color('primary')
                                    ->icon('heroicon-s-document-text')
                                    ->visible(fn($record) => $record->Estado == 2),*/

                Tables\Actions\Action::make('VerComprobante')
                    ->label('Emitir comprobante')
                    ->url(fn($record) => route('comprobante-cuota', $record->id))
//                        ->view('filament.pages.comprobanteFilament', fn($record) => ['record' => $record->id])
                    ->openUrlInNewTab()
                    ->button()
                    ->visible(fn($record) => $record->Estado == 2)
                    ->color('success')
                    ->icon('heroicon-s-document-text'),


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

                        $montoPendiente = $records->sum('Pendiente');
                        $montoTotalPendiente = $montoPendiente - $saldoFavor;

                        $records = $records->sort(function ($a, $b) {
                            // Primero por tipo: ordinaria antes que extraordinaria
                            if ($a->TipoCuota !== $b->TipoCuota) {
                                return $a->TipoCuota === 'cuota_extraordinaria' ? 1 : -1;
                            }

                            // Luego por fecha de vencimiento
                            return $a->fecha_vencimiento <=> $b->fecha_vencimiento;
                        });

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
                                    TextInput::make('Documento')
                                        ->label('N° Documento')
                                        ->required(),
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
                            Forms\Components\Placeholder::make('')
                                ->content('El sistema aplicará el monto ingresado a las cuotas seleccionadas, comenzando por las cuotas ordinarias. Si el monto ingresado excede el total pendiente, se generará un saldo a favor en la ultima cuota saldada. ')

                        ];
                    })
                    ->action(function (array $data, $records) {
                        $saldo = $data['MontoPagar'];
                        $saldoFavor = Cuota::where('idUser', $records->first()->idUser)
                            ->where('SaldoFavor', '>', 0)
                            ->first();

                        $documento = Documentos::create([
                            'TipoDocumento' => 1, // Asumimos que es un comprobante de pago
                            'Nombre' => $data['Documento'],
                            'Path' => $data['DocumentoArchivo'],
                            'Descripcion' => 'Comprobante de pago de cuota',
//                            'AsosiadoA' => Auth::user()->id,
                        ]);

                        // Ordenar las cuotas seleccionadas por tipo y fecha de vencimiento
                        $records = $records->sort(function ($a, $b) {
                            if ($a->TipoCuota !== $b->TipoCuota) {
                                return $a->TipoCuota === 'cuota_extraordinaria' ? 1 : -1;
                            }
                            return $a->fecha_vencimiento <=> $b->fecha_vencimiento;
                        });

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


                                Notification::make()
                                    ->title('Cuota Pagada')
                                    ->body('Se ha pagado la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                                    ->success()
                                    ->duration(5000)
                                    ->icon('heroicon-s-check')
                                    ->send();

                                $record->save();


                            } else {
                                $record->Pendiente = $montoPagar - $saldo;
                                $record->Recaudado = $record->Recaudado + $saldo;
                                $saldo = 0;

                                Notification::make()
                                    ->title('Cuota Abonada')
                                    ->body('Se ha abonado la cuota del periodo ' . Carbon::parse($record->FechaPeriodo)->format('d/m/Y'))
                                    ->success()
                                    ->duration(5000)
                                    ->icon('heroicon-s-check')
                                    ->send();

                                $record->save();

                                break;
                            }

                            DocumentosCuotas::create([
                                'idCuota' => $record->id,
                                'idDocumento' => $documento->id,
                            ]);

                        }
                        if ($saldo > 0) {
                            Notification::make()
                                ->title('Saldo a favor')
                                ->body('Se ha generado un saldo a favor de $' . number_format($saldo, 0, ',', '.'))
                                ->success()
                                ->icon('heroicon-s-check')
                                ->send();
                            $records->last()->update(['SaldoFavor' => $saldo]);
                        }
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
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Estado', 1)),
            'Cuotas Aprobadas' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Estado', '>', 1)),
            'Todas' => Tab::make(),

        ];
    }

}
