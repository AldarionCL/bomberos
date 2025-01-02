<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesLicenciaResource\Pages;
use App\Filament\Resources\SolicitudesLicenciaResource\RelationManagers;
use App\Models\Solicitud;
use App\Models\SolicitudesLicencia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudesLicenciaResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-exclamation-triangle';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Solicitudes Licencia';

    protected static ?string $label = 'Solicitud Licencia';
    protected static ?string $pluralLabel = 'Solicitudes Licencias';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudesLicencias::route('/'),
            'create' => Pages\CreateSolicitudesLicencia::route('/create'),
            'edit' => Pages\EditSolicitudesLicencia::route('/{record}/edit'),
        ];
    }
}
