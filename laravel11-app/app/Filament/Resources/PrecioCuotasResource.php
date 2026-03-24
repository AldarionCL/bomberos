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

    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Valor Cuota';
    protected static ?string $label = 'Valor Cuota';
    protected static ?string $pluralLabel = 'Valores Cuotas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('TipoCuota')
                            ->label('Tipo de Cuota')
                            ->options(fn() => \App\Models\CuotaTipo::where('activo', 1)->pluck('nombre', 'nombre'))
                            ->required(),
                        Forms\Components\Hidden::make('TipoVoluntario')
                            ->default('miembro'),
                        /*Forms\Components\Select::make('TipoVoluntario')
                            ->label('Tipo de Usuario')
                            ->options([
                                "miembro" => "Miembro Oficial",
                                "miembro_honorario" => "Miembro Honorario",
                            ])
                            ->required(),*/
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
                    ])->columns(3)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                /*Tables\Columns\TextColumn::make('TipoVoluntario')
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', strtolower($state))))
                    ->label('Tipo de Usuario'),*/
                Tables\Columns\TextColumn::make('cuotastipo.tipoCobro')
                ->label('Tipo de Cobro'),
                Tables\Columns\TextColumn::make('cuotastipo.nombre')
                    ->formatStateUsing(fn($state) => ucwords(str_replace('_', ' ', strtolower($state))))
                    ->label('Tipo de Cuota'),

                Tables\Columns\TextColumn::make('Monto')
                    ->label('Monto')
                    ->prefix('$')
                    ->numeric(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('TipoCuota')
                    ->options(fn() => \App\Models\CuotaTipo::where('activo', 1)->pluck('nombre', 'nombre')),
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modal(),
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
        ];
    }
}
