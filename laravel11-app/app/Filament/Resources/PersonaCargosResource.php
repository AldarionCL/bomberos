<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonaCargosResource\Pages;
use App\Filament\Resources\PersonaCargosResource\RelationManagers;
use App\Models\PersonaCargo;
use App\Models\PersonaCargos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonaCargosResource extends Resource
{
    protected static ?string $model = PersonaCargo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracion';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Cargo')->required(),
                Forms\Components\TextInput::make('Descripcion'),
                Forms\Components\Toggle::make('Activo')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Cargo'),
                Tables\Columns\TextColumn::make('Descripcion'),
                Tables\Columns\BooleanColumn::make('Activo')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePersonaCargos::route('/'),
        ];
    }
}
