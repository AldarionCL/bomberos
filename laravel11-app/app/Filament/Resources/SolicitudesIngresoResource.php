<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesIngresoResource\Pages;
use App\Filament\Resources\SolicitudesIngresoResource\RelationManagers;
use App\Models\PersonaCargo;
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
use Filament\Forms\Components\Tabs;


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
                Forms\Components\Section::make('Soliciud')->schema([
                    Forms\Components\Select::make('SolicitadoPor')
                        ->relationship('solicitante', 'name')
                        ->disabled(fn($record) => !Auth::user()->isRole('Administrador'))
                        ->label('Solicitado por')
                        ->default(Auth::user()->id),
                    Forms\Components\Select::make('Estado')
                        ->options([
                            0 => 'Pendiente',
                            1 => 'Aprobado',
                            2 => 'Cancelado',
                        ])->default(0)
                        ->live()
                        ->disabled(fn($record) => !Auth::user()->isRole('Administrador')),
                    Forms\Components\Select::make('TipoSolicitud')
                        ->options([
                            1 => 'Ingreso',
                            2 => 'Baja',
                            3 => 'Permiso'
                        ])
                        ->default(2)
                        ->hidden(),
                ])->columns(),

                Forms\Components\Section::make()
                    ->relationship('postulante')
                    ->schema([
                        Forms\Components\Split::make([
                            Tabs::make('TabDatosUsuario')->tabs([
                                Tabs\Tab::make('Datos Generales')->schema([

                                    Forms\Components\TextInput::make('NombrePostulante')
                                        ->required(),
                                    Forms\Components\TextInput::make('RutPostulante')
                                        ->required(),
                                    Forms\Components\TextInput::make('CorreoPostulante')
                                        ->label('Correo Electronico')
                                        ->required(),
                                    Forms\Components\TextInput::make('TelefonoPostulante'),
                                    Flatpickr::make('FechaNacimientoPostulante')
                                        ->label('Fecha Nacimiento')
                                        ->required(),
                                    TextInput::make('EdadPostulante'),
                                    TextInput::make('NacionalidadPostulante'),
                                    Select::make('idCargo')
                                        ->options(fn() => PersonaCargo::where('Activo', 1)->pluck('Cargo', 'id'))
                                        ->label('Cargo')
                                        ->required()
                                ])->icon('fas-user-pen')
                                    ->columns(),
                                Tabs\Tab::make('Datos Personales')->schema([
                                    Forms\Components\TextInput::make('DireccionPostulante'),
                                    Forms\Components\TextInput::make('ComunaPostulante'),
                                    TextInput::make('SituacionMilitarPostulante'),
                                    Forms\Components\Select::make('NivelEstudioPostulante')
                                        ->options([
                                            "basica" => "Basica",
                                            "media" => "Media",
                                            "tecnica" => "Tecnica",
                                            "universitaria" => "Universitaria",
                                        ]),
                                    Forms\Components\TextInput::make('OcupacionPostulante'),
                                    Forms\Components\TextInput::make('LugarOcupacionPostulante'),
                                    Forms\Components\Select::make('EstadoCivilPostulante')
                                        ->options([
                                            'Soltero' => 'Soltero',
                                            'Casado' => 'Casado',
                                            'Divorciado' => 'Divorciado',
                                            'Viudo' => 'Viudo',
                                        ]),
                                    Forms\Components\Select::make('GrupoSanguineoPostulante')
                                        ->options([
                                            "A+" => "A+",
                                            "A-" => "A-",
                                            "B+" => "B+",
                                            "B-" => "B-",
                                            "AB+" => "AB+",
                                            "AB-" => "AB-",
                                            "O+" => "O+",
                                            "O-" => "O-"
                                        ])
                                ])->icon('fas-graduation-cap'),
                                Tabs\Tab::make('Tallas de Ropa')->schema([
                                    Forms\Components\TextInput::make('TallaZapatosPostulante'),
                                    Forms\Components\TextInput::make('TallaPantalonPostulante'),
                                    Forms\Components\TextInput::make('TallaCamisaPostulante'),
                                    Forms\Components\TextInput::make('TallaChaquetaPostulante'),
                                    Forms\Components\TextInput::make('TallaSombreroPostulante'),
                                ])->icon('fas-shirt'),
                                Tabs\Tab::make('Observaciones')->schema([
                                    Forms\Components\Textarea::make('Observaciones')
                                        ->rows(5)
                                        ->columnSpanFull()
                                ])->icon('fas-comment')
                            ])->columns(),

                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\FileUpload::make('FotoPostulante')
                                        ->disk('public')
                                        ->directory('fotosPersonas')
                                        ->avatar()
                                        ->imageEditor()
                                        ->preserveFilenames()
                                        ->moveFiles()
                                        ->previewable()
                                        ->deletable(true)
                                    ,
                                ])->grow(false)

                        ])->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Documentos')->schema([
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
                Tables\Columns\Layout\Split::make([
                    TextColumn::make('id')
                        ->description('ID', position: 'above')
                        ->label('ID'),
                    TextColumn::make('postulante.NombrePostulante')
                        ->description('Postulante', position: 'above')
                        ->label('Nombre')
                        ->searchable(),
                    TextColumn::make('Fecha_registro')
                        ->description('Fecha Solicitud', position: 'above')
                        ->label('Fecha Registro')
                        ->date('d/m/Y'),

                    Tables\Columns\TextColumn::make('Estado')
                        ->state(fn($record) => ($record->Estado === 0) ? 'Pendiente' : 'Aprobado')
                        ->color(fn($state) => $state == 'Aprobado' ? 'success' : 'danger')
                        ->icon(fn($state) => $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                        ->badge()
                        ->label('Estado'),
                ])

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->button(),
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
