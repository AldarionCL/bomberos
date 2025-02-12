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
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CuotasPersonaResource extends Resource
{
    protected static ?string $model = Persona::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?string $navigationLabel = 'Recaudacion';
    protected static ?string $label = 'Recaudacion por Persona';

    public static function canAccess(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero') || Auth::user()->id == $record->idUsuario;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Persona')
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
            ->modifyQueryUsing(function (Builder $query) {
                if (!Auth::user()->isRole('Administrador') && !Auth::user()->isCargo('Tesorero')) {
                    $query->where('idUsuario', Auth::id());
                }
            })
            ->columns([
                Tables\Columns\Layout\Split::make([

                    TextColumn::make('Rut')
                        ->searchable()
                        ->sortable()
                        ->visibleFrom('md'),
                    TextColumn::make('user.name')
//                        ->description(fn($record) => $record->Rut)
                        ->label('Nombre')
                        ->searchable()
                        ->sortable(),
                    TextColumn::make('estado.Estado')
                        ->visibleFrom('md')
                        ->grow(false),
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('contCuotas')
                            ->badge()
                            ->color(fn($record) => $record->cuotas->where('Estado', '1')->count() > 0 ? 'logoYellow' : 'logoBlue')
                            ->prefix("Cuotas Pendientes: ")
                            ->default(fn($record) => $record->cuotas->where('Estado', '1')->count())
                            ->label('Cuotas Pendientes')
                            ->grow(false),
                        TextColumn::make('montoPendiente')
                            ->default(fn($record) => $record->cuotas->where('Estado', 1)->sum('Monto'))
                            ->prefix('$')
                            ->money('CLP')
                        ->grow(false),
                    ])->alignment(Alignment::End),

                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Pagar')
                    ->button(),
//                Tables\Actions\ViewAction::make(),
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
            RelationManagers\CuotasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCuotasPersonas::route('/'),
            'create' => Pages\CreateCuotasPersona::route('/create'),
            'edit' => Pages\EditCuotasPersona::route('/{record}/edit'),
            'view' => Pages\ViewCuotasPersona::route('/{record}/view'),
        ];
    }
}
