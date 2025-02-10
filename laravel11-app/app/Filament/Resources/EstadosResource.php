<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EstadosResource\Pages;
use App\Filament\Resources\EstadosResource\RelationManagers;
use App\Models\Estados;
use App\Models\PersonaEstado;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EstadosResource extends Resource
{
    protected static ?string $model = PersonaEstado::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Administracion';
    protected static ?string $navigationLabel = 'Estados Personas';
    protected static ?string $label = 'Estado';
    protected static ?string $pluralLabel = 'Estados';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Estado'),
                Forms\Components\TextInput::make('Descripcion'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Estado'),
                TextColumn::make('Descripcion'),
            ])
            ->checkIfRecordIsSelectableUsing(fn($record)=>!$record->Locked)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn($record)=>$record->Locked),
            ])
            ->recordUrl(null)
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
            'index' => Pages\ListEstados::route('/'),
            'create' => Pages\CreateEstados::route('/create'),
            'edit' => Pages\EditEstados::route('/{record}/edit'),
        ];
    }
}
