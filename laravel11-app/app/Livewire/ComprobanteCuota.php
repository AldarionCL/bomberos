<?php

namespace App\Livewire;

use App\Models\Persona;
use Livewire\Component;

class ComprobanteCuota extends Component
{
    public $records;
    public $cuota;
    public $documento;
    public $user;
    public $aprobador;
    public $aprobadorNombre;
    public $logoBase64;

    public function mount($idDocumento)
    {
        $this->records = \App\Models\Cuota::where('idDocumento', $idDocumento)->get();
        $this->cuota = $this->records[0];
        $this->documento = $this->cuota->documento;
        $this->user = $this->cuota->user;
        $this->aprobador = $this->cuota->aprobador;
        $this->aprobadorNombre = $this->aprobador ? 'Directiva2026' : null;

        if ($this->aprobador && $this->aprobador->name == 'Admin') {
            $tesorero = Persona::whereHas('cargo', fn($query) => $query->where('Cargo', 'Tesorero'))->first()?->user;
            if ($tesorero) {
                $this->aprobador = $tesorero;
            }
        }

        $logoPath = public_path('img/logo.png');
        $this->logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
            : null;
    }

    public function render()
    {
        return view('livewire.comprobante-cuota');
    }

    public static function getHtml($idDocumento)
    {
        $records = \App\Models\Cuota::where('idDocumento', $idDocumento)->get();
        $cuota = $records[0];

        return view('livewire.comprobante-cuota', [
            'cuota' => $cuota,
            'records' => $records,
            'documento' => $cuota->documento,
            'user' => $cuota->user,
            'aprobador' => $cuota->aprobador,
            'aprobadorNombre' => $cuota->aprobador ? 'Directiva2026' : null,
        ])->render();
    }
}
