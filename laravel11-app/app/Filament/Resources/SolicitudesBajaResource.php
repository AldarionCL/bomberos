<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesBajaResource\Pages;
use App\Filament\Resources\SolicitudesBajaResource\RelationManagers;
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

class SolicitudesBajaResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-minus';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Solicitudes Baja';

    protected static ?string $label = 'Solicitud Baja';
    protected static ?string $pluralLabel = 'Solicitudes Bajas';
    protected static ?int $navigationSort = 3;

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
                                1 => 'Ingreso',
                                2 => 'Baja',
                                3 => 'Permiso'
                            ])
                            ->default(2)
                            ->hidden(),

                    ])->columns(),

                Forms\Components\Section::make('Datos del Voluntario')
                    ->schema([
                        Forms\Components\Select::make('AsociadoA')
                            ->relationship('asociado', 'name')
                            ->label('Voluntario')
                            ->hint('Seleccione un Voluntario para asociar a esta solicitud')
                            ->required(),
                        Forms\Components\RichEditor::make('Observaciones')
                    ])->columns(),

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
                return $query->where('TipoSolicitud', 1);
            })
            ->columns([
                TextColumn::make('id')->label('ID'),
                Tables\Columns\ImageColumn::make('aprobador.persona.Foto')
                    ->defaultImageUrl(url('/storage/fotosPersonas/placeholderAvatar.png'))
                    ->circular()
                    ->grow(false)
                    ->label('Foto'),
                TextColumn::make('asociado.name')->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Estado')
                    ->state(fn ($record) => ($record->Estado === 0) ? 'Pendiente' : 'Aprobado')
                    ->badge()
                    ->color(fn($state)=> $state == 'Aprobado' ? 'success' : 'danger')
                    ->icon(fn($state)=> $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                    ->label('Estado'),
                TextColumn::make('solicitante.name')->label('Solicitado por:'),
                TextColumn::make('Fecha_registro')
                    ->label('Fecha de Solicitud')
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
            'index' => Pages\ListSolicitudesBajas::route('/'),
            'create' => Pages\CreateSolicitudesBaja::route('/create'),
            'edit' => Pages\EditSolicitudesBaja::route('/{record}/edit'),
        ];
    }
}
