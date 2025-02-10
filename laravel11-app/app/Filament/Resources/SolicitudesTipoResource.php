<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesTipoResource\Pages;
use App\Filament\Resources\SolicitudesTipoResource\RelationManagers;
use App\Models\SolicitudesTipo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudesTipoResource extends Resource
{
    protected static ?string $model = SolicitudesTipo::class;
    protected static ?string $navigationGroup = 'Administracion';

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Tipo')->required(),
                Forms\Components\TextInput::make('Descripcion'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Tipo'),
                Tables\Columns\TextColumn::make('Descripcion'),
            ])
            ->checkIfRecordIsSelectableUsing(fn($record)=>!$record->Locked)
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->disabled(fn($record)=>$record->Locked),
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
            'index' => Pages\ManageSolicitudesTipos::route('/'),
        ];
    }
}
