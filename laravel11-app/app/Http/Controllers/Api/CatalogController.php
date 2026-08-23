<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentosTipo;
use App\Models\Persona;
use App\Models\PersonaCargo;
use App\Models\PersonaEstado;
use App\Models\UserRole;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $tiposDocumento = $user->isRole('Administrador')
            ? DocumentosTipo::orderBy('Tipo')->get(['id', 'Tipo', 'Clasificacion'])
            : DocumentosTipo::where('Clasificacion', 'publico')->orderBy('Tipo')->get(['id', 'Tipo', 'Clasificacion']);

        $data = [
            'cargos' => PersonaCargo::where('Activo', 1)->orderBy('Cargo')->get(['id', 'Cargo']),
            'roles' => UserRole::orderBy('Rol')->get(['id', 'Rol']),
            'estados' => PersonaEstado::orderBy('Estado')->get(['id', 'Estado']),
            'tiposDocumento' => $tiposDocumento,
        ];

        if ($user->can('update', Persona::class)) {
            $data['tiposDocumentoPrivado'] = DocumentosTipo::where('Clasificacion', 'privado')
                ->orderBy('Tipo')->get(['id', 'Tipo', 'Clasificacion']);
        }

        return response()->json($data);
    }
}
