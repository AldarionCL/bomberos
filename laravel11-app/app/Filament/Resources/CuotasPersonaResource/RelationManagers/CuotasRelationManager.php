<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuotasRelationManager extends RelationManager
{
    protected static string $relationship = 'cuotas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
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
                            ->label('Estado'),
                    ])->columns(),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('Pendiente')
                            ->prefix('$')
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
                            ->label('Fecha de Pago'),

                        Forms\Components\TextInput::make('Documento')
                            ->label('N° Documento'),

                        Forms\Components\FileUpload::make('DocumentoArchivo')
                            ->label('Archivo'),
                    ])->columns()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Cuotas')
            ->columns([
                Tables\Columns\TextColumn::make('FechaPeriodo')->date(),
                Tables\Columns\TextColumn::make('FechaVencimiento')->date(),
                Tables\Columns\TextColumn::make('FechaPago')->date(),
                Tables\Columns\TextColumn::make('estadocuota.Estado')->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'badgeAlert',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Cancelado' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('Pendiente'),
                Tables\Columns\TextColumn::make('Recaudado'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Ingresar Pago'),
//                Tables\Actions\DeleteAction::make(),
                /*Tables\Actions\Action::make('IngresarPago')
                    ->url(fn ($url) => route('filament.cuotas-pago', $url))
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                ->label('Ingresar Pago'),*/
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
