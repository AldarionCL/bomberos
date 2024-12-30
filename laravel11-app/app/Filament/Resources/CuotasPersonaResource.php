<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuotasPersonaResource\Pages;
use App\Filament\Resources\CuotasPersonaResource\RelationManagers;
use App\Models\CuotasPersona;
use App\Models\Persona;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuotasPersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('Rut'),
                        Forms\Components\Placeholder::make('user.name'),
                        Forms\Components\Placeholder::make('estado.Estado'),
                        Forms\Components\Placeholder::make('contCuotas')
                            ->default(fn($record) => $record->cuotas->where('Estado', 1)->count()),
                    ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Rut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado.Estado'),
                TextColumn::make('contCuotas')
                    ->badge()
                    ->color('info')
                    ->default(fn($record) => $record->cuotas->where('Estado', '1')->count())
                    ->label('Cuotas Pendientes'),
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
            'index' => Pages\ListCuotasPersonas::route('/'),
            'create' => Pages\CreateCuotasPersona::route('/create'),
            'edit' => Pages\EditCuotasPersona::route('/{record}/edit'),
        ];
    }
}