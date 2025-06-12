<?php

namespace App\Filament\Imports;

use App\Models\Persona;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;

class PersonasImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping(),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping(), // Ensure email is unique
            ImportColumn::make('Rut')
                ->label('Rut'),
            ImportColumn::make('Cargo')
                ->castStateUsing(function ($state) {
                    if (blank($state)) {
                        return 11;
                    } else {
                        $cargo = \App\Models\PersonaCargo::where('Cargo', $state)->first();
                        if ($cargo) return $cargo->id; else return 11;
                    }
                })
                ->requiredMapping()
                ->label('Cargo'),
            ImportColumn::make('FechaNacimiento')
                ->label('Fecha de Nacimiento'),
            ImportColumn::make('Sexo')
                ->label('Sexo'),
            ImportColumn::make('Telefono')
                ->label('Teléfono'),
            ImportColumn::make('EstadoCivil')
                ->label('Estado Civil'),
            ImportColumn::make('Nacionalidad')
                ->label('Nacionalidad'),
            ImportColumn::make('GrupoSanguineo')
                ->label('Grupo Sanguíneo'),
            ImportColumn::make('Direccion')
                ->label('Dirección'),

        ];

    }

    public function resolveRecord(): ?User
    {

        $registro = User::firstOrCreate([
            'email' => $this->data['email']
        ], [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => bcrypt('12345678'), // Default password, should be changed by user
            'idRole' => 2
        ]);

        $persona = Persona::firstOrCreate([
            'idUsuario' => $registro->id
        ], [
            'idUsuario' => $registro->id,
            'idCargo' => $this->data['Cargo'], // Voluntario
            'idEstado' => 1, // Assuming a default state
            'Activo' => 1, // Assuming new users are active by default
            'Rut' => $this->data['Rut'] ?? '1-9',
        ]);
        if ($persona) {
            if (isset($this->data['FechaNacimiento'])) {
                $persona->FechaNacimiento = \Carbon\Carbon::parse($this->data['FechaNacimiento']);
            }
            if (isset($this->data['Sexo'])) {
                $persona->Sexo = $this->data['Sexo'];
            }
            if (isset($this->data['Telefono'])) {
                $persona->Telefono = $this->data['Telefono'];
            }
            if (isset($this->data['EstadoCivil'])) {
                $persona->EstadoCivil = $this->data['EstadoCivil'];
            }
            if (isset($this->data['Nacionalidad'])) {
                $persona->Nacionalidad = $this->data['Nacionalidad'];
            }
            if (isset($this->data['GrupoSanguineo'])) {
                $persona->GrupoSanguineo = $this->data['GrupoSanguineo'];
            }

            if (isset($this->data['Direccion'])) {
                $persona->Direccion = $this->data['Direccion'];
            }
        }
        unset($this->data['Rut']);
        unset($this->data['Cargo']);
        unset($this->data['FechaNacimiento']);
        unset($this->data['Sexo']);
        unset($this->data['Telefono']);
        unset($this->data['EstadoCivil']);
        unset($this->data['Nacionalidad']);
        unset($this->data['GrupoSanguineo']);
        unset($this->data['Direccion']);
        $persona->save();


        return $registro;

//        return new User();
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importacion ha sido completada con : ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' importados.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' fallaron la importacion.';
        }

        return $body;
    }
}
