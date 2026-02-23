<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CajaResource\Pages;
use App\Models\Caja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\CajaStats;

class CajaResource extends Resource
{
    protected static ?string $model = Caja::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?int $navigationSort = 1;
    protected static ?string $label = 'Caja';
    protected static ?string $pluralLabel = 'Caja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('descripcion')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('monto')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('impuesto')
                            ->numeric()
                            ->prefix('$')
                            ->default(0),
                        Forms\Components\TextInput::make('total')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Select::make('tipo')
                            ->required()
                            ->options([
                                'Ingreso' => 'Ingreso',
                                'Egreso' => 'Egreso',
                            ]),
                        Forms\Components\Select::make('id_usuario')
                            ->relationship('usuario', 'name')
                            ->label('Usuario')
                            ->searchable()
                            ->placeholder('Seleccione un usuario'),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monto')
                    ->money('CLP', locale: 'es-CL')
                    ->sortable()
                    ->color(fn(string $state): string => str_starts_with($state, '-') ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('impuesto')
                    ->money('CLP', locale: 'es-CL')
                    ->sortable()
                    ->color(fn(string $state): string => str_starts_with($state, '-') ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('total')
                    ->money('CLP', locale: 'es-CL')
                    ->sortable()
                    ->color(fn(string $state): string => str_starts_with($state, '-') ? 'danger' : 'success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Ingreso cuota' => 'success',
                        'Egreso' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'Ingreso cuota' => 'Ingreso cuota',
                        'Egreso' => 'Egreso',
                    ]),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getHeaderWidgets(): array
    {
        return [
            CajaStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCajas::route('/'),
            'create' => Pages\CreateCaja::route('/create'),
        ];
    }
}
