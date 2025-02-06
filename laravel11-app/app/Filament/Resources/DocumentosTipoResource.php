<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentosTipoResource\Pages;
use App\Filament\Resources\DocumentosTipoResource\RelationManagers;
use App\Models\DocumentosTipo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentosTipoResource extends Resource
{
    protected static ?string $model = DocumentosTipo::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracion';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Tipo'),
                Forms\Components\TextInput::make('Descripcion'),
                Forms\Components\Select::make('Clasificacion')
                ->options([
                    'publico' => 'Publico',
                    'privado' => 'Privado',
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Tipo')
                ->searchable(),
                Tables\Columns\TextColumn::make('Clasificacion')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'privado' => 'badgeAlert',
                        'publico' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('Descripcion'),

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
            'index' => Pages\ManageDocumentosTipos::route('/'),
        ];
    }
}
