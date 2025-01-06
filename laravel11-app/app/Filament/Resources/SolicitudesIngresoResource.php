<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesIngresoResource\Pages;
use App\Filament\Resources\SolicitudesIngresoResource\RelationManagers;
use App\Models\Solicitud;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SolicitudesIngresoResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-plus';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Solicitudes Ingreso';

    protected static ?string $label = 'Solicitud Ingreso';
    protected static ?string $pluralLabel = 'Solicitudes Ingresos';

    protected static ?int $navigationSort = 1;

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
                            ->live()
                            ->disabled(fn($record) => Auth::user()->isRole('admin')),
                        Forms\Components\Select::make('TipoSolicitud')
                            ->options([
                                1 => 'Ingreso',
                                2 => 'Baja',
                                3 => 'Permiso'
                            ])
                            ->default(2)
                            ->hidden(),

                    ])->columns(),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Datos del Voluntario')
                            ->schema([
                                Forms\Components\TextInput::make('RutPostulante')
                                    ->required(),
                                Forms\Components\TextInput::make('NombrePostulante')
                                    ->required(),
                                Forms\Components\TextInput::make('TelefonoPostulante'),
                                Forms\Components\TextInput::make('CorreoPostulante')
                                    ->required(),
                                Flatpickr::make('FechaNacimientoPostulante')
                                    ->label('Fecha Nacimiento')
                                    ->required(),
                                TextInput::make('EdadPostulante'),
                                TextInput::make('NacionalidadPostulante'),
                                Forms\Components\Select::make('EstadoCivilPostulante')
                                    ->options([
                                        'Soltero' => 'Soltero',
                                        'Casado' => 'Casado',
                                        'Divorciado' => 'Divorciado',
                                        'Viudo' => 'Viudo',
                                    ]),
                                TextInput::make('SituacionMilitarPostulante'),
                                Forms\Components\TextInput::make('DireccionPostulante'),
                                Forms\Components\TextInput::make('NivelEstudioPostulante'),

                                Forms\Components\TextInput::make('OcupacionPostulante')
                            ])->columns()
                        ->compact(),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Foto')
                            ->schema([
                                Forms\Components\FileUpload::make('FotoPostulante')
                                    ->disk('public')
                                    ->directory('fotosPersonas')
                                    ->avatar()
                                    ->moveFiles()
                                    ->previewable()
                                ->deletable(true),
                            ])
                    ]),


                Forms\Components\Section::make('Documentos')
                    ->schema([
                        Forms\Components\Repeater::make('Documentos')
                            ->relationship('documentos')
                            ->schema([
                                Select::make('TipoDocumento')
                                    ->relationship('tipo', 'Tipo'),
                                TextInput::make('Nombre'),
                                Forms\Components\FileUpload::make('Path')
                                    ->inlineLabel(true)
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->directory('documentos')
                                ,
                            ])->columns(3)
                            ->defaultItems(0),

                    ])->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->where('TipoSolicitud', 2);
            })
            ->columns([
                TextColumn::make('id')->label('ID'),
                TextColumn::make('NombrePostulante')->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Estado')
                    ->state(fn($record) => ($record->Estado === 0) ? 'Pendiente' : 'Aprobado')
                    ->color(fn($state)=> $state == 'Aprobado' ? 'success' : 'danger')
                    ->icon(fn($state)=> $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                    ->badge()
                    ->label('Estado'),
                TextColumn::make('Fecha_registro')
                    ->label('Fecha Registro')
                    ->date('d/m/Y'),
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
            RelationManagers\AprobacionesRelationManager::class,
//            RelationManagers\DocumentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudesIngresos::route('/'),
            'create' => Pages\CreateSolicitudesIngreso::route('/create'),
            'edit' => Pages\EditSolicitudesIngreso::route('/{record}/edit'),
        ];
    }
}
