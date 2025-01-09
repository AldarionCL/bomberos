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
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class DocumentosResource extends Resource
{
    protected static ?string $model = Documentos::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationLabel = 'Documentos';

    protected static ?string $label = 'Documento';
    protected static ?string $pluralLabel = 'Documentos';

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
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\IconColumn::make('extension')
                    ->icon('heroicon-o-document')
                    ->default(1),
                    Tables\Columns\TextColumn::make('Nombre')->searchable()
                    ->description(fn($record)=>$record->tipo->Tipo),
//                    Tables\Columns\TextColumn::make('tipo.Tipo'),

//                Tables\Columns\TextColumn::make('Path'),
                    Tables\Columns\TextColumn::make('asociado.name'),
                    Tables\Columns\TextColumn::make('solicitud.id'),
                    Tables\Columns\TextColumn::make('created_at')
                    ->date("d/m/Y"),
                ])
            ])->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('TipoDocumento')
                ->relationship('tipo', 'Tipo')
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->button(),
                Tables\Actions\Action::make('descargar')
                ->action(fn($record)=>Storage::disk('public')->download($record->Path))
                    ->icon('heroicon-s-arrow-down-on-square')
                    ->color('info')
                ->button()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
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
