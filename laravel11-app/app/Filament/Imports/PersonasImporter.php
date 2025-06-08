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
                ->relationship('persona', 'Rut')
                ->label('Rut')
                ->requiredMapping(),
//            ImportColumn::make('password')
//                ->requiredMapping()
//                ->label('Password'),
        ];
    }

    public function resolveRecord(): ?User
    {
//        dump($this->data);

        $registro = User::firstOrCreate([
            'email' => $this->data['email']
        ], [
            'name' => $this->data['name'],
            'email' => $this->data['email'],
            'password' => bcrypt('12345678'), // Default password, should be changed by user
            'idRole' => 2
        ]);


        if ($registro) {
            if (!Persona::where('idUsuario', $registro->id)->first()) {
                $persona = Persona::create([
                    'idUsuario' => $registro->id,
                    'idCargo' => 11, // Assuming a default cargo
                    'idEstado' => 1, // Assuming a default state
                    'Activo' => 1, // Assuming new users are active by default
                    'Rut' => $this->data['Rut'] ?? '1-9',
                ]);
                Log::info("Created new Persona for user ID: " . $registro->id);

            } else {
                Log::info("Persona already exists for user ID: " . $registro->id);
            }

        }

        return $registro;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
