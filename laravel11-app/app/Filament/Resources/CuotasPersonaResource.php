<?php

namespace App\Filament\Resources;

use App\Filament\Pages\ComprobantePago;
use App\Filament\Resources\CuotasPersonaResource\Pages;
use App\Filament\Resources\CuotasPersonaResource\RelationManagers;
use App\Models\CuotasPersona;
use App\Models\Persona;
use Carbon\Carbon;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconPosition;
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
                Tables\Columns\ImageColumn::make('Foto')
                    ->defaultImageUrl(url('/storage/fotosPersonas/placeholderAvatar.png'))
                    ->circular()
                    ->grow(false),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nombre')
                    ->description(fn($record) => $record->estado->Estado)
                    ->searchable()
                    ->sortable()
                    ->grow(false),

                Tables\Columns\TextColumn::make('Rut')
                    ->label('Rut')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contCuotas')
                    ->badge()
                    ->color(fn($record) => $record->cuotasMes->where('Estado', '1')->count() > 0 ? 'logoYellow' : 'logoBlue')
                    ->prefix("Cuotas Pendientes: ")
                    ->state(fn($record) => $record->cuotasMes->where('Estado', '1')->count())
                    ->label('Cuotas Pendientes Mes')
                    ->grow(false),

                TextColumn::make('montoPendiente')
                    ->label('Monto Pendiente Mes')
                    ->default(fn($record) => $record->cuotasMes->where('Estado', 1)->sum('Monto'))
                    ->prefix('$')
                    ->money('CLP')
                    ->grow(false),
//                    ->visible(fn($record) => ($record->Edad ?? 0) < 50),

                Tables\Columns\TextColumn::make('Exento')
                    ->icon('heroicon-s-check-circle')
                    ->color('danger')
                    ->default(fn($record) => $record->Edad >= 50 ? 'Exento' : '')
                    ->tooltip('Exento de pago por edad')
//                    ->visible(fn($record) => ($record->Edad ?? 0) >= 50),

            ])
            ->filters([
                //
            ])
            ->actions([
                /*Tables\Actions\EditAction::make()
                    ->label('Ver cuotas')
                    ->icon('heroicon-s-eye')
                    ->button(),*/
                Tables\Actions\ViewAction::make(),
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
