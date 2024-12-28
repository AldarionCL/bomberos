<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TesoreriaResource\Pages;
use App\Filament\Resources\TesoreriaResource\RelationManagers;
use App\Models\Cuota;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TesoreriaResource extends Resource
{
    protected static ?string $model = Cuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tesoreria';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Select::make('idUser')
                            ->relationship('user', 'name')
                            ->label('Persona')
                            ->required(),

                        Forms\Components\TextInput::make('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required(),

//                    DatePicker::make('fechaPeriodo')->label('Fecha de Periodo'),
                        Flatpickr::make('FechaPeriodo')
                            ->label('Periodo Desde')
                            ->required(),

                        Flatpickr::make('FechaVencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required(),

                        Select::make('Estado')
                            ->relationship('estadocuota', 'Estado')
                            ->default(1)
                            ->label('Estado'),

                        Flatpickr::make('FechaPago')->label('Fecha de Pago'),

                        Forms\Components\TextInput::make('Documento')
                            ->label('N° Documento'),

                        Forms\Components\FileUpload::make('DocumentoArchivo')
                            ->label('Archivo'),

                    ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.persona.Rut')
                    ->label('Rut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')->label('Persona')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('FechaPeriodo')
                    ->label('Periodo')
                    ->date('m/Y')
                    ->sortable(),
                TextColumn::make('FechaVencimiento')
                    ->label('Fecha Vencimiento')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('estadocuota.Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'warning',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Cancelado' => 'danger',
                        default => 'gray',
                    })
                    ->label('Estado'),
                /*TextColumn::make('Monto')
                    ->label('Monto')
                    ->prefix("$")
                    ->money('CLP')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('$')
                            ->money('CLP')
                            ->label('Total')
                    ]),*/
                TextColumn::make('Pendiente')
                    ->prefix("$")
                    ->money('CLP')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('$')
                            ->money('CLP')
                            ->label('Total')
                    ]),
                TextColumn::make('Recaudado')
                    ->prefix("$")
                    ->money('CLP')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('$')
                            ->money('CLP')
                            ->label('Total')
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('idUsuario')
                    ->relationship('user', 'name')
                    ->searchable()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('user.name')
                ->label('Nombre'),
                Tables\Grouping\Group::make('estadocuota.Estado')
                ->label('Estado')
            ])
            ->defaultGroup('user.name')
            ->defaultSort(fn($query) => $query->orderBy('idUser', 'desc')->orderBy('FechaPeriodo', 'asc'))
            ->paginated(25);
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
            'index' => Pages\ListTesorerias::route('/'),
            'create' => Pages\CreateTesoreria::route('/create'),
            'edit' => Pages\EditTesoreria::route('/{record}/edit'),
        ];
    }
}
