<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use App\Filament\Pages\ComprobantePago;
use App\Filament\Resources\CuotasPersonaResource;
use App\Livewire\ComprobanteCuota;
use App\Models\Cuota;
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
                    ])->columns(3),
                Forms\Components\Section::make('Pago')
                    ->schema([
                        TextInput::make('Monto')
                            ->label('Monto de cuota')
                            ->prefix('$')
                            ->readOnly(),
                        Forms\Components\TextInput::make('Pendiente')
                            ->prefix('$')
                            ->suffixAction(Forms\Components\Actions\Action::make('aplicar')
                                ->icon('heroicon-m-arrow-right-circle')
                                ->action(function ($record, $set) {
                                    $set('Recaudado', $record->Pendiente + $record->Recaudado);
                                    $set('Pendiente', 0);
                                    $set('SaldoFavor', 0);
//                                    $set('Estado', 2);
                                })
                                ->disabled(fn($record) => $record->Estado != 1)
                            )
                            ->readOnly()
                            ->reactive(),
                        Forms\Components\TextInput::make('Recaudado')
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->hint('Ingrese el monto recaudado')
                            ->hintColor('warning')
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                /*if ($state > $record->Monto) {
                                    $state = $record->Monto;
                                    $set('Recaudado', $record->Monto);
                                }*/

                                $monto = $record->Monto - $state;
                                $saldo = $state - $record->Monto;
                                $set('Pendiente', max($monto, 0));
                                $set('SaldoFavor', max($saldo, 0));
                                /*if ($get('Pendiente') == 0) {
                                    $set('Estado', 2);
                                } else {
                                    $set('Estado', 1);
                                }*/
                            }),
                        Forms\Components\TextInput::make('SaldoFavor')
                            ->prefix('$')
                            ->reactive()
                            ->readOnly()
                            ->default(fn($record) => $record->Recaudado - $record->Monto),

                    ])->columns(),

                Section::make('Comprobante')
                    ->schema([
                        Forms\Components\TextInput::make('Documento')
                            ->label('N° Documento')
                            ->required()
                            ->visibleOn('edit'),
                        Flatpickr::make('FechaPago')->label('Fecha de Pago')
                            ->default(fn() => Carbon::today()->format('Y-m-d'))
                            ->required()
                            ->visibleOn('edit'),
                        Forms\Components\FileUpload::make('DocumentoArchivo')
                            ->label('Archivo Comprobante')
                            ->required()
                            ->disk('public')
                            ->directory('comprobantesCuotas')
                            ->deletable(false)
                            ->previewable()
                            ->downloadable()
                            ->visibleOn('edit')
                        ->columnSpanFull(),

                    ])->columns(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Cuotas')
            ->columns([

                Tables\Columns\Layout\Split::make([

                    Tables\Columns\Layout\Panel::make([
                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('FechaPeriodo')
                                ->description('Periodo', position: 'above')
                                ->date("d/m/Y"),
//                                ->prefix("Periodo: "),
                            Tables\Columns\TextColumn::make('FechaVencimiento')
                                ->description('Fecha Vencimiento', position: 'above')
                                ->date("d/m/Y")
//                                ->prefix("Vencimiento: "),
                        ]),
                    ]),
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('TipoCuota')
//                            ->description('Tipo de Cuota', position: 'above')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'cuota_ordinaria' => 'Cuota Ordinaria',
                                'cuota_extraordinaria' => 'Cuota Extraordinaria',
                                default => $state,
                            }),
                        Tables\Columns\TextColumn::make('Pendiente')
                            ->prefix("Pendiente : $")
                            ->color('warning'),
                        Tables\Columns\TextColumn::make('Recaudado')
                            ->prefix("Recaudado : $")
                            ->color('success'),
                    ]),
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
                        ->description('Fecha de Pago', position: 'above')
                        ->date("d/m/Y")
                        ->visibleFrom('md'),
                ])
            ])
            ->filters([
                //
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
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'Pendientes' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Estado', 1)),
            'Pagados' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('Estado', '>', 1)),
            'Todos' => Tab::make(),

        ];
    }

}
