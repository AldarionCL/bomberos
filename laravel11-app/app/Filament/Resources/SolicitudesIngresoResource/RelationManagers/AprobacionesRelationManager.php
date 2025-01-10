<?php

namespace App\Filament\Resources\SolicitudesIngresoResource\RelationManagers;

use App\Models\Aprobaciones;
use App\Models\Persona;
use App\Models\Solicitud;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AprobacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'aprobaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('idAprobador')
                    ->relationship('aprobador', 'name')
                    ->required(),
                Forms\Components\Toggle::make('Estado')
                    ->label('Aprobado')
                    ->onColor('success')
                    ->onIcon('heroicon-s-check')
                    ->offColor('danger')
                    ->offIcon('heroicon-s-x-mark')
                    ->inline(false)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Aprobadores')
            ->columns([
                Tables\Columns\TextColumn::make('Orden')
                    ->label('Nivel de Aprobación'),

                Tables\Columns\TextColumn::make('aprobador.name'),
                /*Tables\Columns\ToggleColumn::make('Estado')
                    ->onColor('success')
                    ->onIcon('heroicon-s-check')
                    ->offColor('danger')
                    ->offIcon('heroicon-s-x-mark')
                ->disabled(fn ($record) => ($record->idAprobador == Auth::user()->id) ? false : true),*/
                Tables\Columns\TextColumn::make('EstadoSol')
                    ->default(fn($record) => $record->Estado == 1 ? 'Aprobado' : 'Pendiente')
                    ->color(fn($state)=> $state == 'Aprobado' ? 'success' : 'danger')
                    ->icon(fn($state)=> $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                    ->label('Estado Aprobación')
                    ->badge(),
                Tables\Columns\TextColumn::make('FechaAprobacion')
                    ->date('d/m/Y')
                ->label('Fecha de Aprobación'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('aprobar')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->action(function ($record) {
                        $record->Estado = 1;
                        $record->FechaAprobacion = date('Y-m-d');
                        $record->save();

                        if(Aprobaciones::where('idSolicitud', $record->idSolicitud)->where('Estado', 0)->count() == 0){
                            $solicitud = Solicitud::where('id', $record->idSolicitud)->first();
                            $solicitud->Estado = 1;
                            $solicitud->save();

                            $usuario = User::create([
                                'name' => $solicitud->NombrePostulante,
                                'email' => $solicitud->EmailPostulante,
                                'password' => Hash::make('password'),
                            ]);

                            Persona::create([
                                'idUsuario' => $usuario->id,
                                'idCargo' => 1,
                                'idEstado' => 1,
                                'Rut' => $solicitud->RutPostulante,
                                'Telefono' => $solicitud->TelefonoPostulante,
                                'Direccion' => $solicitud->DireccionPostulante,
                                'FechaNacimiento' => $solicitud->FechaNacimientoPostulante,
                                'Sexo' => $solicitud->SexoPostulante,
                                'EstadoCivil' => $solicitud->EstadoCivilPostulante,
                                'Ocupacion' => $solicitud->OcupacionPostulante,
                                'Activo' => 1
                            ]);

                            $solicitud->documentos->update([
                                'AsociadoA' => $usuario->id,
                            ]);

                            Notification::make()
                                ->title('Solicitud Aprobada')
                                ->success()
                                ->icon('heroicon-s-check')
                                ->send();

                            $this->redirect('edit', Solicitud::find($record->idSolicitud));
                        }
                    })
                    ->requiresConfirmation()
                    ->disabled(function($record) {
                        if ($record->Estado == 0) {
                            if ($record->idAprobador == Auth::user()->id){
                                return false;
                            } else{
                                return true;
                            }
                        } else {
                            return true;
                        }

                    }),

                Tables\Actions\EditAction::make()
                    ->button()
                    ->hidden(fn($record) => Auth::user()->isRole('admin')),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn($record) => !Auth::user()->isRole('admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
