<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AprobadoresResource\Pages;
use App\Filament\Resources\AprobadoresResource\RelationManagers;
use App\Models\Aprobadores;
use App\Models\SolicitudesTipo;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AprobadoresResource extends Resource
{
    protected static ?string $model = SolicitudesTipo::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Administracion';
    protected static ?string $navigationLabel = 'Aprobadores';
    protected static ?string $label = 'Aprobadores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tipo Solicitud')
                    ->schema([
                        Forms\Components\Placeholder::make('Tipo')
                            ->content(fn($state) => $state)
                            ->label('Tipo Solicitud'),
                        Forms\Components\Placeholder::make('Descripcion')
                            ->content(fn($state) => $state),
                    ])->columns(),
                Forms\Components\Section::make('Aprobadores')
                    ->schema([
                        Repeater::make('Aprobador')
                            ->relationship('aprobadores')
                            ->schema([
                                Select::make('Orden')
                                    ->options([
                                        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5
                                    ])
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                                Forms\Components\Select::make('idAprobador')
                                    ->relationship('aprobador', 'name')
                            ])->columns()
                            ->grid()
                            ->orderColumn('Orden')
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Tipo')
                    ->label('Tipo Solicitud')
                    ->searchable(),
                TextColumn::make('contAprobadores')
                    ->default(fn($record) => $record->aprobadores->count())
                    ->label('Aprobadores')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListAprobadores::route('/'),
            'create' => Pages\CreateAprobadores::route('/create'),
            'edit' => Pages\EditAprobadores::route('/{record}/edit'),
        ];
    }
}
