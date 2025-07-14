<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrecioCuotasResource\Pages;
use App\Filament\Resources\PrecioCuotasResource\RelationManagers;
use App\Models\PrecioCuotas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class   PrecioCuotasResource extends Resource
{
    protected static ?string $model = PrecioCuotas::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Administracion';
    protected static ?string $navigationLabel = 'Valor Cuotas';
    protected static ?string $label = 'Valor Cuota';
    protected static ?string $pluralLabel = 'Valores Cuotas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('TipoCuota')
                    ->label('Tipo de Cuota')
                    ->options([
                        'cuota_ordinaria' => 'Cuota Ordinaria',
                        'cuota_extraordinaria' => 'Cuota Extraordinaria',
                    ])
                    ->required(),
                Forms\Components\Select::make('TipoVoluntario')
                    ->label('Tipo de Voluntario')
                    ->options([
                        "voluntario" => "Voluntario",
                        "voluntario_honorario" => "Voluntario Honorario",
                        "voluntario_jubilado" => "Voluntario Jubilado",
                        "voluntario_estudiante" => "Voluntario Estudiante",
                        "miembro_honorario" => "Miembro Honorario",
                    ])
                    ->required(),
                Forms\Components\TextInput::make('Monto')
                    ->label('Monto')
                    ->numeric()
                    ->required()
                    ->prefix('$'),
                /*Forms\Components\DatePicker::make('periodo')
                    ->label('Periodo Desde')
                    ->required()
                    ->default(now())
                    ->displayFormat('d/m/Y'),*/
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('TipoVoluntario')
                    ->label('Tipo de Voluntario'),
                Tables\Columns\TextColumn::make('TipoCuota')
                    ->label('Tipo de Cuota'),

                Tables\Columns\TextColumn::make('Monto')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrecioCuotas::route('/'),
            'create' => Pages\CreatePrecioCuotas::route('/create'),
            'edit' => Pages\EditPrecioCuotas::route('/{record}/edit'),
        ];
    }
}
