<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticiasResource\Pages;
use App\Filament\Resources\NoticiasResource\RelationManagers;
use App\Models\DocumentosTipo;
use App\Models\Noticias;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NoticiasResource extends Resource
{
    protected static ?string $model = Noticias::class;

    protected static ?string $navigationIcon = 'heroicon-s-newspaper';
    protected static ?string $navigationGroup = 'Administracion';


    protected static ?string $label = 'Publicación';
    protected static ?string $pluralLabel = 'Publicaciones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('Titulo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('Subtitulo')
                            ->columnSpan(2),
                        Forms\Components\MarkdownEditor::make('Contenido')
                            ->required()
                            ->columnSpan(2),
                        Flatpickr::make('FechaPublicacion')
                            ->default(fn() => now()->format('Y-m-d'))
                            ->required(),
                        Flatpickr::make('FechaExpiracion'),
                        Forms\Components\Select::make('Estado')
                            ->options([
                                1 => 'Publicado',
                                2 => 'Agendado',
                                3 => 'Expirado',
                            ])
                            ->default(1),
                        Forms\Components\FileUpload::make('Imagen'),

                    ])->columns(),
                Forms\Components\Section::make('Documento')
                    ->relationship('documento')
                    ->schema([
                        Forms\Components\TextInput::make('Nombre'),
                        Forms\Components\TextInput::make('Descripcion'),
                        Forms\Components\Select::make('TipoDocumento')
                            ->options(fn() => DocumentosTipo::where('Clasificacion', 'publico')->pluck('Tipo', 'id')->toArray()),
                        Forms\Components\FileUpload::make('Path')
                            ->disk('public')
                            ->label('Archivo')
                            ->downloadable()
                            ->deletable(false)
                            ->required(),
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Titulo')
                    ->searchable(),
                TextColumn::make('Subtitulo'),
                Tables\Columns\BooleanColumn::make('Estado'),
                Tables\Columns\TextColumn::make('FechaPublicacion')
                    ->sortable()
                    ->date(),
                Tables\Columns\TextColumn::make('FechaExpiracion')
                    ->sortable()
                    ->date(),
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
            'index' => Pages\ListNoticias::route('/'),
            'create' => Pages\CreateNoticias::route('/create'),
            'edit' => Pages\EditNoticias::route('/{record}/edit'),
        ];
    }
}
