<div>
    <div class="grid-cols-1 container mx-auto grid md:grid-cols-1">
        @foreach($noticias as $noticia)
            <livewire:cardNoticia :noticia="$noticia" wire:key="{{$noticia->id}}"/>
        @endforeach

    </div>
    <div class="mt-6 pt-4">
        <x-filament::pagination :paginator="$noticias" :current-page-option-property="$perPage"/>
    </div>
</div>
