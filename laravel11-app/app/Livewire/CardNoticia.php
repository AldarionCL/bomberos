<?php

namespace App\Livewire;

use Livewire\Component;

class CardNoticia extends Component
{
    public $noticia;
    public function render()
    {
        return view('livewire.card-noticia');
    }
}
