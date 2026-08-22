<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentosTipo;
use App\Models\PersonaCargo;
use App\Models\PersonaEstado;
use App\Models\UserRole;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        $tiposDocumento = $request->user()->isRole('Administrador')
            ? DocumentosTipo::orderBy('Tipo')->get(['id', 'Tipo', 'Clasificacion'])
            : DocumentosTipo::where('Clasificacion', 'publico')->orderBy('Tipo')->get(['id', 'Tipo', 'Clasificacion']);

        return response()->json([
            'cargos' => PersonaCargo::where('Activo', 1)->orderBy('Cargo')->get(['id', 'Cargo']),
            'roles' => UserRole::orderBy('Rol')->get(['id', 'Rol']),
            'estados' => PersonaEstado::orderBy('Estado')->get(['id', 'Estado']),
            'tiposDocumento' => $tiposDocumento,
        ]);
    }
}
