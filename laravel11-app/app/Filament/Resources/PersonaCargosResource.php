<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonaCargosResource\Pages;
use App\Filament\Resources\PersonaCargosResource\RelationManagers;
use App\Models\PersonaCargo;
use App\Models\PersonaCargos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PersonaCargosResource extends Resource
{
    protected static ?string $model = PersonaCargo::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracion';
    protected static ?string $navigationLabel = 'Cargos';
    protected static ?string $label = 'Cargo';
    protected static ?string $pluralLabel = 'Cargos';

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
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\TextColumn::make('Cargo')
                        ->weight(FontWeight::Bold)
                        ->description(fn($record): string => $record->Descripcion ?? '')
                        ->searchable(),
//                    Tables\Columns\TextColumn::make('Descripcion'),
                    Tables\Columns\TextColumn::make('Activo')
                        ->state(fn($record) => $record->Activo == 1 ? 'Activo' : 'Inactivo')
                        ->badge()
                        ->icon(fn($record) => $record->Activo == 1 ? 'fas-check-circle' : 'fas-x-circle')
                        ->color(fn($record) => $record->Activo == 1 ? 'info' : 'warning')
                        ->grow(false)
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->button(),
//                Tables\Actions\DeleteAction::make(),
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
