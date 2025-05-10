<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonasResource\Pages;
use App\Filament\Resources\PersonasResource\RelationManagers;
use App\Models\DocumentosTipo;
use App\Models\PersonaCargo;
use App\Models\PersonaEstado;
use App\Models\User;
use App\Models\UserRole;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PersonasResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-group';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Listado Voluntarios';
    protected static ?string $label = 'Voluntario';
    protected static ?string $pluralLabel = 'Voluntarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required(),
                        Forms\Components\TextInput::make('email')->required(),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->autocomplete(false)
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state)),
                        Forms\Components\Select::make('idRole')
                            ->label('Rol')
                            ->options(fn() => UserRole::all()->pluck('Rol', 'id'))
                            ->visible(fn() => Auth::user()->isRole('Administrador'))
                    ])->columns(),

                Forms\Components\Section::make()
                    ->relationship('persona')
                    ->schema([
//                        Forms\Components\Split::make([
                        Tabs::make('TabDatosUsuario')->tabs([
                            Tabs\Tab::make('Datos Generales')->schema([

                                Forms\Components\TextInput::make('Rut')
                                    ->required(),
                                Forms\Components\TextInput::make('Telefono'),
                                Flatpickr::make('FechaNacimiento')
                                    ->label('Fecha Nacimiento')
                                    ->required(),
                                TextInput::make('Edad'),
                                TextInput::make('Nacionalidad'),
                                Select::make('idCargo')
                                    ->options(fn() => PersonaCargo::where('Activo', 1)->pluck('Cargo', 'id'))
                                    ->label('Cargo')
                                    ->required(),
                                Select::make('idEstado')
                                    ->label('Estado')
                                    ->options(fn() => PersonaEstado::all()->pluck('Estado', 'id'))
                                    ->default(1),
                                Forms\Components\Toggle::make('Activo')
                                    ->inline(false)
                                    ->default(true)
                            ])->columns()
                                ->icon('fas-user-pen'),
                            Tabs\Tab::make('Datos Personales')->schema([
                                Forms\Components\TextInput::make('Direccion'),
                                Forms\Components\TextInput::make('Comuna'),
                                TextInput::make('SituacionMilitar'),
                                Forms\Components\Select::make('NivelEstudio')
                                    ->options([
                                        "basica" => "Basica",
                                        "media" => "Media",
                                        "tecnica" => "Tecnica",
                                        "universitaria" => "Universitaria",
                                    ]),
                                Forms\Components\TextInput::make('Ocupacion'),
                                Forms\Components\TextInput::make('LugarOcupacion'),
                                Forms\Components\Select::make('EstadoCivil')
                                    ->options([
                                        'Soltero' => 'Soltero',
                                        'Casado' => 'Casado',
                                        'Divorciado' => 'Divorciado',
                                        'Viudo' => 'Viudo',
                                    ]),
                                Forms\Components\Select::make('GrupoSanguineo')
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
                                Forms\Components\TextInput::make('TallaZapatos'),
                                Forms\Components\TextInput::make('TallaPantalon'),
                                Forms\Components\TextInput::make('TallaCamisa'),
                                Forms\Components\TextInput::make('TallaChaqueta'),
                                Forms\Components\TextInput::make('TallaSombrero'),
                            ])->icon('fas-shirt'),
                            Tabs\Tab::make('Observaciones')->schema([
                                Forms\Components\Textarea::make('Observaciones')
                                    ->rows(5)
                                    ->columnSpanFull()
                            ])->icon('fas-comment'),

                            Tabs\Tab::make('Documentos')->schema([
                                Forms\Components\Repeater::make('Documentos')
                                    ->relationship('documentos')
                                    ->schema([
                                        Select::make('TipoDocumento')
                                            ->options(fn() => DocumentosTipo::where('Clasificacion', 'privado')->pluck('Tipo', 'id')),
                                        TextInput::make('Nombre'),
                                        Forms\Components\FileUpload::make('Path')
                                            ->inlineLabel(false)
                                            ->label('Archivo')
                                            ->disk('public')
                                            ->downloadable()
                                            ->previewable()
                                            ->directory('documentos')
                                            ->required(),
                                    ])->columnSpanFull()
                                    ->columns(3)
                                    ->defaultItems(0),
                            ])->icon('fas-file')
                        ])->columns(),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\FileUpload::make('Foto')
                                    ->disk('public')
                                    ->directory('fotosPersonas')
                                    ->avatar()
                                    ->imageEditor()
                                    ->preserveFilenames()
                                    ->moveFiles()
                                    ->previewable()
                                    ->deletable(true),
                            ])->grow(false)

                    ]),
//                    ]),
            ]);


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\ImageColumn::make('persona.Foto')
                            ->defaultImageUrl(url('/storage/fotosPersonas/placeholderAvatar.png'))
                            ->circular()
                            ->grow(false),

                        Tables\Columns\Layout\Stack::make([
                            Tables\Columns\TextColumn::make('name')
                                ->label('Nombre')
                                ->searchable()
                                ->sortable(),

                            Tables\Columns\TextColumn::make('persona.Rut')
                                ->label('Rut')
                                ->searchable()
                                ->sortable(),
                        ]),
                    ]),

                    Tables\Columns\TextColumn::make('email')
                        ->description('Email', position: 'above')
                        ->searchable()
                        ->sortable(),

                    Tables\Columns\TextColumn::make('persona.cargo.Cargo')
                        ->description('Cargo', position: 'above'),

                    Tables\Columns\TextColumn::make('persona.estado.Estado')
                        ->badge()
                        ->color(fn(string $state): string => match ($state) {
                            'Activo' => 'info',
                            'Licencia' => 'warning',
                            'Baja' => 'danger',
                            default => 'gray',
                        }),

                    Tables\Columns\TextColumn::make('created_at')
                        ->date("d/m/Y")
                        ->description('Fecha Creacion', position: 'above')
                        ->label('Creado')
                        ->visibleFrom('md'),
                ])


            ])
            ->filters([
                Tables\Filters\SelectFilter::make('idCargo')
                    ->relationship('persona.cargo', 'Cargo')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPersonas::route('/'),
            'create' => Pages\CreatePersonas::route('/create'),
            'edit' => Pages\EditPersonas::route('/{record}/edit'),
        ];
    }
}
