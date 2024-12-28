<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NoticiasResource\Pages;
use App\Filament\Resources\NoticiasResource\RelationManagers;
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

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'Administracion';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('titulo')
                            ->required()
                            ->maxLength(255)
                        ->columnSpan(2),
                        Forms\Components\TextInput::make('subtitulo')
                        ->columnSpan(2),
                        Forms\Components\MarkdownEditor::make('contenido')
                            ->required()
                        ->columnSpan(2),
                        Flatpickr::make('FechaPublicacion')->required(),
                        Flatpickr::make('FechaExpiracion'),
                        Forms\Components\Select::make('estado')
                        ->options([
                            1 => 'Publicado',
                            2 => 'Agendado',
                            3 => 'Expirado',
                        ]),
                        Forms\Components\FileUpload::make('imagen'),

                    ])->columns(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo'),
                TextColumn::make('descripcion'),
                Tables\Columns\TextColumn::make('Estado'),
                Tables\Columns\TextColumn::make('FechaPublicacion')
                    ->date(),
                Tables\Columns\TextColumn::make('FechaExpiracion')
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