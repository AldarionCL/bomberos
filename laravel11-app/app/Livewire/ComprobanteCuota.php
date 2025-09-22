<?php

namespace App\Livewire;

use Livewire\Component;

class ComprobanteCuota extends Component
{

    public $records;
    public $cuota;
    public $documento;
    public $user;
    public $aprobador;

    public function mount($idDocumento)
    {
        $this->records = \App\Models\Cuota::where('idDocumento', $idDocumento)->get();
        $this->cuota = $this->records[0];
        $this->documento = $this->cuota->documento;
        $this->user = $this->cuota->user;
        $this->aprobador = $this->cuota->aprobador;
    }

    public function render()
    {
        return view('livewire.comprobante-cuota');
    }

/*    public function createPDF(){
        $pdf = \PDF::loadView('livewire.comprobante-cuota', ['cuota' => $this->cuota]);
        return $pdf->stream('comprobante-cuota.pdf');
    }*/

    public static function getHtml($idDocumento){
        $records = \App\Models\Cuota::where('idDocumento', $idDocumento)->get();
        $cuota = $records[0];

        return view('livewire.comprobante-cuota', [
            'cuota' => $cuota,
            'records' => $records,
            'documento' => $cuota->documento,
            'user' => $cuota->user,
            'aprobador' => $cuota->aprobador,
        ])->render();
    }

}
