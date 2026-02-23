<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CuotasPendientesResource\Pages;
use App\Models\Cuota;
use App\Models\CuotasEstados;
use App\Models\Persona;
use App\Filament\Resources\CuotasPersonaResource;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CuotasPendientesResource extends Resource
{
    protected static ?string $model = Cuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationLabel = 'Cuotas Pendientes';
    protected static ?string $label = 'Cuota Pendiente';
    protected static ?string $pluralLabel = 'Cuotas Pendientes';

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
                        Forms\Components\Select::make('idUser')
                            ->relationship('user', 'name')
                            ->label('Persona')
                            ->disabled(),
                        Forms\Components\TextInput::make('TipoCuota')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'cuota_ordinaria' => 'Cuota Ordinaria',
                                'cuota_extraordinaria' => 'Cuota Extraordinaria',
                                default => ucwords(str_replace('_', ' ', strtolower($state))),
                            })
                            ->disabled(),
                        Forms\Components\TextInput::make('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\Select::make('Estado')
                            ->options(fn() => CuotasEstados::all()->pluck('Estado', 'id'))
                            ->label('Estado'),
                        Forms\Components\DatePicker::make('FechaPeriodo')
                            ->label('Periodo')
                            ->format('m/Y')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->selectRaw('MIN(id) as id, idUser, COUNT(*) as cuotas_pendientes, SUM(Pendiente) as total_pendiente')
                ->whereHas('estadocuota', function ($query) {
                    $query->whereIn('Estado', ['Pendiente', 'Pendiente Aprobacion']);
                })
                ->where('FechaVencimiento', '<=', Carbon::now()->endOfDay())
                ->groupBy('idUser')
            )
            ->columns([
                TextColumn::make('user.persona.Rut')
                    ->label('Rut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cuotas_pendientes')
                    ->label('Cuotas pendientes')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_pendiente')
                    ->label('Total Pendiente')
                    ->money('CLP', locale: 'es_CL')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('idUser')
                    ->relationship('user', 'name')
                    ->label('Usuario')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_cuotas')
                    ->label('Ver Cuotas Persona')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->button()
                    ->url(fn (Cuota $record): string => CuotasPersonaResource::getUrl('view', ['record' => $record->user->persona->id])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('user.name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCuotasPendientes::route('/'),
        ];
    }
}
