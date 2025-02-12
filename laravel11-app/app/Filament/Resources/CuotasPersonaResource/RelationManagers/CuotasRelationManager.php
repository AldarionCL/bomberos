<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CuotasRelationManager extends RelationManager
{
    protected static string $relationship = 'cuotas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacion Cuota')
                    ->schema([
                        Forms\Components\Placeholder::make('Monto')
                            ->content(fn($record) => "$" . $record->Monto),

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
                    ])->columns(),
                Forms\Components\Section::make('Pago')
                    ->schema([
                        Forms\Components\TextInput::make('Pendiente')
                            ->prefix('$')
                            ->suffixAction(Forms\Components\Actions\Action::make('aplicar')
                                ->icon('heroicon-m-arrow-right-circle')
                                ->action(function ($record, $set) {
                                    $set('Recaudado', $record->Pendiente);
                                    $set('Pendiente', 0);
                                    $set('Estado', 2);
                                })
                            )
                            ->readOnly()
                            ->reactive(),
                        Forms\Components\TextInput::make('Recaudado')
                            ->prefix('$')
                            ->reactive()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($state > $record->Monto) {
                                    $state = $record->Monto;
                                    $set('Recaudado', $record->Monto);
                                }

                                $monto = $record->Monto - $state;
                                $set('Pendiente', $monto);
                                if ($get('Pendiente') == 0) {
                                    $set('Estado', 2);
                                } else {
                                    $set('Estado', 1);
                                }
                            }),


                        Flatpickr::make('FechaPago')
                            ->label('Fecha de Pago')
                            ->default(fn() => Carbon::today()->format('Y-m-d')),

                        Forms\Components\TextInput::make('Documento')
                            ->label('N° Documento'),

                        Forms\Components\FileUpload::make('DocumentoArchivo')
                            ->downloadable()
                            ->previewable()
                            ->deletable(false)
                            ->label('Archivo'),
                    ])->columns()
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
                    ->color('info'),
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('AprobarPago')
                    ->action(function ($record) {
                        $record->update(['Estado' => 2]);

                        Notification::make()
                            ->title('Pago Aprobado')
                            ->success()
                            ->icon('heroicon-s-check')
                            ->send();
                    })
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->disabled(fn($record) => !((Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero'))))
                    ->requiresConfirmation()

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
