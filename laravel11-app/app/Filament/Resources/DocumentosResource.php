<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentosResource\Pages;
use App\Filament\Resources\DocumentosResource\RelationManagers;
use App\Models\Documentos;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentosResource extends Resource
{
    protected static ?string $model = Documentos::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('Nombre')
                            ->required(),

                        Forms\Components\Select::make('TipoDocumento')
                            ->relationship('tipo', 'Tipo')
                            ->label('Tipo de Documento')
                            ->required(),

                        Forms\Components\Textarea::make('Descripcion'),

                        Forms\Components\FileUpload::make('Path')
                            ->disk('public')
                            ->label('Archivo')
                            ->downloadable()
                            ->deletable(false)
                            ->required(),
                    ])->columns(),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('AsociadoA')
                            ->relationship('asociado', 'name')
                        ->hint('Si el documento esta asociado a un usuario, seleccionar del listado. De lo contrario, dejar en blanco'),
                    ]),
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Toggle::make('Noticia')
                            ->hint('Generar Noticia sobre este documento')
                            ->label('Publicar')
                            ->default(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Nombre'),
                Tables\Columns\TextColumn::make('Descripcion')
                    ->words(100),
                Tables\Columns\TextColumn::make('tipo.Tipo'),

//                Tables\Columns\TextColumn::make('Path'),
                Tables\Columns\TextColumn::make('asociado.name'),
                Tables\Columns\TextColumn::make('created_at'),
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
            'index' => Pages\ListDocumentos::route('/'),
            'create' => Pages\CreateDocumentos::route('/create'),
            'edit' => Pages\EditDocumentos::route('/{record}/edit'),
        ];
    }
}
