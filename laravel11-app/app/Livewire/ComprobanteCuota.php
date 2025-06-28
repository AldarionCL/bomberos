<?php

namespace App\Livewire;

use Livewire\Component;

class ComprobanteCuota extends Component
{

    public $record;
    public $cuota;

    public function mount($record)
    {
        $this->record = $record;
        $this->cuota = \App\Models\Cuota::find($record);
    }

    public function render()
    {
        return view('livewire.comprobante-cuota');
    }

/*    public function createPDF(){
        $pdf = \PDF::loadView('livewire.comprobante-cuota', ['cuota' => $this->cuota]);
        return $pdf->stream('comprobante-cuota.pdf');
    }*/

    public static function getHtml($id){
        $cuota = \App\Models\Cuota::find($id);

        return view('livewire.comprobante-cuota', ['cuota' => $cuota]);
    }

}
