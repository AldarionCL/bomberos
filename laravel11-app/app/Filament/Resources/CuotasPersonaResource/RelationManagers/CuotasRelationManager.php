<?php

namespace App\Filament\Resources\CuotasPersonaResource\RelationManagers;

use Filament\Forms;
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
                Forms\Components\TextInput::make('Cuotas')
                    ->required()
                    ->maxLength(255),
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
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('IngresarPago')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                ->label('Ingresar Pago'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
