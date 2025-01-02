<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesBajaResource\Pages;
use App\Filament\Resources\SolicitudesBajaResource\RelationManagers;
use App\Models\Solicitud;
use App\Models\SolicitudesBaja;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SolicitudesBajaResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-minus';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Solicitudes Baja';

    protected static ?string $label = 'Solicitud Baja';
    protected static ?string $pluralLabel = 'Solicitudes Bajas';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Soliciud')
                ->schema([
                    Forms\Components\Select::make('SolicitadoPor')
                        ->relationship('solicitante', 'name')
                        ->disabled()
                        ->label('Solicitado por')
                        ->default(Auth::user()->id),
                    Forms\Components\Select::make('Estado')
                        ->options([
                            0 => 'Pendiente',
                            1 => 'Aprobado',
                            2 => 'Cancelado',
                        ])->default(0)
                    ->disabled(),
                    Forms\Components\Select::make('TipoSolicitud')
                    ->options([
                        1=>'Ingreso',
                        2=>'Baja',
                        3=>'Permiso'
                    ])
                    ->default(2)
                    ->hidden(),

                ])->columns(),

                Forms\Components\Section::make('Datos del Voluntario')
                ->schema([
                    Forms\Components\TextInput::make('NombrePostulante')
                    ->required(),
                    Forms\Components\TextInput::make('TelefonoPostulante'),
                    Forms\Components\TextInput::make('CorreoPostulante')
                    ->required(),
                    Forms\Components\TextInput::make('DireccionPostulante'),
                    Forms\Components\TextInput::make('NivelEstudioPostulante'),
                    Flatpickr::make('FechaNacimientoPostulante')
                    ->label('Fecha Nacimiento')
                    ->required(),
                    Forms\Components\TextInput::make('OcupacionPostulante')
                ])->columns()

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('NombrePostulante')->label('Nombre'),
                Tables\Columns\TextColumn::make('Estado')->label('Estado'),
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
            RelationManagers\DocumentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudesBajas::route('/'),
            'create' => Pages\CreateSolicitudesBaja::route('/create'),
            'edit' => Pages\EditSolicitudesBaja::route('/{record}/edit'),
        ];
    }
}
