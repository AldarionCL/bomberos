<?php

namespace App\Filament\Resources\SolicitudesLicenciaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class DocumentosRelationManager extends RelationManager
{
    protected static string $relationship = 'documentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('Nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('Descripcion'),
                Forms\Components\Select::make('TipoDocumento')
                    ->relationship('tipo', 'Tipo')
                    ->label('Tipo Documento'),
                Forms\Components\FileUpload::make('Path')
                    ->downloadable()
                    ->label('Documento'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Documentos')
            ->columns([
                Tables\Columns\TextColumn::make('tipo.Tipo'),
                Tables\Columns\TextColumn::make('Nombre'),
                Tables\Columns\TextColumn::make('Path')
                    ->badge()
                    ->url(fn ($record) => $record->path, true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn()=>Auth::user()->isRole('Administrador')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
