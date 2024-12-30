<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuotasPersonaResource\Pages;
use App\Filament\Resources\CuotasPersonaResource\RelationManagers;
use App\Models\CuotasPersona;
use App\Models\Persona;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CuotasPersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?string $navigationLabel = 'Cuotas x Persona';
    protected static ?string $label = 'Cuota';
    protected static ?string $pluralLabel = 'Cuotas';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('Rut')
                            ->content(fn($record) => $record->Rut),
                        Forms\Components\Placeholder::make('name')
                            ->content(fn($record) => $record->user->name)
                            ->label('Nombre'),
                        Forms\Components\Placeholder::make('Estado')
                            ->content(fn($record) => $record->estado->Estado),
                        Forms\Components\Placeholder::make('contCuotas')
                            ->content(fn($record) => $record->cuotas->where('Estado', 1)->count())
                        ->label('Cuotas Pendientes'),
                    ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('Rut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('estado.Estado'),
                TextColumn::make('contCuotas')
                    ->badge()
                    ->color('info')
                    ->default(fn($record) => $record->cuotas->where('Estado', '1')->count())
                    ->label('Cuotas Pendientes'),
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
            RelationManagers\CuotasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuotasPersonas::route('/'),
            'create' => Pages\CreateCuotasPersona::route('/create'),
            'edit' => Pages\EditCuotasPersona::route('/{record}/edit'),
        ];
    }
}
