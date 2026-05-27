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
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Egresos';
    protected static ?string $label = 'Egreso';
    protected static ?string $pluralLabel = 'Egresos';

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
                            ->label('Tipo de Egreso')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('MontoGasto')
                            ->label('Monto Egreso')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('MontoTotal', (float)$state);
                            }),
                        Forms\Components\TextInput::make('MontoTotal')
                            ->hidden()
                            ->dehydrated()
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Forms\Set $set, $state) {
                                $set('MontoGasto', (float)$state);
                            }),
                        Forms\Components\Textarea::make('Descripcion')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('Evento')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('FechaGasto')
                            ->label('Fecha del Egreso')
                            ->required()
                            ->default(now()),
                        Forms\Components\TextInput::make('Evento')
                            ->label('Asociado a')
                            ->maxLength(255),
                        Forms\Components\FileUpload::make('documento.ruta_archivo')
                            ->label('Documento Adjunto')
                            ->disk('public')
                            ->directory('gastos-documentos')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('FechaGasto')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('TipoGasto')
                    ->label('Tipo de Egreso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Descripcion')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('Evento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('MontoGasto')
                    ->label('Monto Egreso')
                    ->money('CLP')
                    ->sortable()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('Evento')
                    ->label('Asociado a')
                    ->searchable(),
                Tables\Columns\IconColumn::make('documento.ruta_archivo')
                    ->label('Adjunto')
                    ->icon(fn ($state) => $state ? 'heroicon-o-document-check' : 'heroicon-o-document-minus')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->url(fn ($record) => $record->documento ? asset('storage/' . $record->documento->ruta_archivo) : null)
                    ->openUrlInNewTab(),
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
