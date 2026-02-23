<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GastosResource\Pages;
use App\Filament\Resources\GastosResource\RelationManagers;
use App\Models\Gastos;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Filament\Widgets\GastosStats;
use Illuminate\Support\Facades\Auth;

class GastosResource extends Resource
{
    protected static ?string $model = Gastos::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?string $navigationLabel = 'Gastos';
    protected static ?string $label = 'Gasto';
    protected static ?string $pluralLabel = 'Gastos';

    public static function canAccess(): bool
    {
        return Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('TipoGasto')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('MontoGasto')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state, Forms\Get $get) {
                                $iva = (float)$state * 0.19;
                                $set('MontoIva', $iva);
                                $set('MontoTotal', (float)$state + $iva);
                            }),
                        Forms\Components\TextInput::make('MontoIva')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('MontoTotal')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\Textarea::make('Descripcion')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\DatePicker::make('FechaGasto')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('AsociadoA')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('FechaGasto')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('TipoGasto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('MontoGasto')
                    ->money('CLP')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('MontoIva')
                    ->money('CLP')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('MontoTotal')
                    ->money('CLP')
                    ->sortable()
                    ->color('danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('AsociadoA')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('FechaGasto')
                    ->form([
                        Forms\Components\DatePicker::make('desde'),
                        Forms\Components\DatePicker::make('hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('FechaGasto', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('FechaGasto', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getHeaderWidgets(): array
    {
        return [
            GastosStats::class,
        ];
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
            'index' => Pages\ListGastos::route('/'),
            'create' => Pages\CreateGastos::route('/create'),
            'edit' => Pages\EditGastos::route('/{record}/edit'),
        ];
    }
}
