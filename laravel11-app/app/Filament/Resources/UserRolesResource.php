<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserRolesResource\Pages;
use App\Filament\Resources\UserRolesResource\RelationManagers;
use App\Models\UserRole;
use App\Models\UserRoles;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserRolesResource extends Resource
{
    protected static ?string $model = UserRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Rol'),
                TextInput::make('Descripcion'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Rol'),
                Tables\Columns\TextColumn::make('Descripcion'),
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
            'index' => Pages\ListUserRoles::route('/'),
            'create' => Pages\CreateUserRoles::route('/create'),
            'edit' => Pages\EditUserRoles::route('/{record}/edit'),
        ];
    }
}
