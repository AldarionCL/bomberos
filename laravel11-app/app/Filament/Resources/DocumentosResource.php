<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentosResource\Pages;
use App\Filament\Resources\DocumentosResource\RelationManagers;
use App\Models\Documentos;
use App\Models\DocumentosTipo;
use Faker\Provider\Text;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
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
                            ->options(fn() => DocumentosTipo::where('Clasificacion', 'publico')->pluck('Tipo', 'id'))
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
            ->modifyQueryUsing(function ($query) {
                if (!Auth::user()->isRole('Administrador')) {
                    $query->whereHas('tipo', function ($query) {
                        return $query->where('Clasificacion', 'publico');
                    });
                }
                return $query->orderBy('created_at', 'desc');
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\IconColumn::make('')
                        ->icon(function ($record) {
                            $extension = explode(".", $record->Path);
                            if ($extension[1] == "pdf") {
                                return 'fas-file-pdf';
                            } else if ($extension[1] == "png" || $extension[1] == "jpg" || $extension[1] == "jpeg") {
                                return 'fas-image';
                            } else if ($extension[1] == "docx") {
                                return 'fas-file-word';
                            } else if ($extension[1] == "xlsx") {
                                return 'fas-file-excel';
                            } else {
                                return 'fas-file';
                            }
                        })
                        ->default(1)
                        ->grow(false),
                    Tables\Columns\TextColumn::make('Nombre')->searchable()
                        ->description(fn($record) => $record->tipo->Tipo)
                    ,
//                Tables\Columns\TextColumn::make('tipo.Tipo')->searchable(),

                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('tipo.Clasificacion')
                            ->label('Acceso')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'privado' => 'badgeAlert',
                                'publico' => 'success',
                                default => 'gray',
                            })
                            ->visible(fn() => Auth::user()->isRole('Administrador')),

                        TextColumn::make('asociado.name')
                            ->visible(fn() => Auth::user()->isRole('Administrador')),
                    ])->alignment(Alignment::End),

                    Tables\Columns\TextColumn::make('created_at')
                        ->label('Fecha Creacion')
                        ->date("d/m/Y")
                        ->weight(FontWeight::Thin),
                ])
            ])
            ->recordUrl(function ($record) {
                return APP::make('url')->to('storage/' . $record->Path);
            }, true)
            ->filters([
                Tables\Filters\SelectFilter::make('TipoDocumento')
                    ->options(fn() => Auth::user()->isRole('Administrador')
                        ? DocumentosTipo::all()->pluck('Tipo', 'id')
                        : DocumentosTipo::where('Clasificacion', 'publico')->pluck('Tipo', 'id')
                    ),

            ], FiltersLayout::AboveContent)
            ->actions([
//                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('descargar')
                    ->action(fn($record) => Storage::disk('public')->download($record->Path))
                    ->icon('heroicon-s-arrow-down-on-square')
                    ->color('info')
                    ->button()
                    ->size('xs'),
                Tables\Actions\EditAction::make()
                    ->button()
                    ->size('xs')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'view' => Pages\ViewDocumentos::route('/{record}'),
            'edit' => Pages\EditDocumentos::route('/{record}/edit'),
        ];
    }
}
