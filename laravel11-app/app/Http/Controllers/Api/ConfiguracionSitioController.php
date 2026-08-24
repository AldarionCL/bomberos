<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracionSitio;
use Illuminate\Http\Request;

class ConfiguracionSitioController extends Controller
{
    private function serialize(ConfiguracionSitio $config): array
    {
        return [
            'nombreGrupo' => $config->nombre_grupo,
            'logo' => $config->logo ? asset('storage/'.$config->logo) : null,
            'color' => $config->color,
        ];
    }

    /** Público: lo necesita hasta la pantalla de login, antes de autenticarse. */
    public function show()
    {
        return response()->json($this->serialize(ConfiguracionSitio::actual()));
    }

    public function update(Request $request)
    {
        abort_unless($request->user()->isRole('Administrador'), 403);

        $data = $request->validate([
            'nombreGrupo' => ['required', 'string', 'max:255'],
            'color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $config = ConfiguracionSitio::actual();

        $config->nombre_grupo = $data['nombreGrupo'];
        $config->color = $data['color'];

        if ($request->hasFile('logo')) {
            $config->logo = $request->file('logo')->store('sitio', 'public');
        }

        $config->save();

        return response()->json($this->serialize($config));
    }
}
