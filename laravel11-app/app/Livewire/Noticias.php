<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Noticias extends Component
{
    use withPagination;

    public int|string $perPage = 6;
    public string $tipoNoticia = 'todos';

    public function render()
    {

        $noticias = \App\Models\Noticias::where('Estado', 1)
            ->where('created_at', '<>', null)
            ->orderBy('created_at', 'desc')
            ->cursorPaginate($this->perPage);

        return view('livewire.noticias')
            ->with('noticias', $noticias);
    }

    public function mount()
    {

    }

    public function nextPage()
    {
        $this->perPage += 10;
    }
}
