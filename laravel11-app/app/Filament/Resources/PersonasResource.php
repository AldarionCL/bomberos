<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonasResource\Pages;
use App\Filament\Resources\PersonasResource\RelationManagers;
use App\Models\User;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class PersonasResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Administracion';
    protected static ?string $navigationLabel = 'Personal';
    protected static ?string $label = 'Personal';
    protected static ?string $pluralLabel = 'Personal';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('email')->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state)),
                    ])->columns(),
                Forms\Components\Section::make('Datos Personales')
                    ->schema([
                        Forms\Components\TextInput::make('Rut')
                            ->required(),
                        Forms\Components\TextInput::make('Telefono'),
                        Forms\Components\TextInput::make('Direccion'),

                        Forms\Components\Select::make('idCargo')
                            ->relationship('cargo', 'Cargo')
                            ->label('Cargo')
                            ->required(),

                        Forms\Components\Select::make('idEstado')
                            ->relationship('estado', 'Estado')
                            ->required(),
                    ])->relationship('persona')
                    ->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('persona.Rut')
                    ->label('Rut')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('persona.cargo.Cargo'),
                Tables\Columns\TextColumn::make('persona.estado.Estado'),
                Tables\Columns\TextColumn::make('created_at')->label('Creado'),

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
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersonas::route('/create'),
            'edit' => Pages\EditPersonas::route('/{record}/edit'),
        ];
    }
}
