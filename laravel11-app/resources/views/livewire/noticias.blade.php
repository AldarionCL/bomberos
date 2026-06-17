<div class="space-y-10">

@php
    $items   = collect($noticias->items());
    $featured = $items->first();
    $rest    = $items->slice(1);

    function noticia_initials($name) {
        return collect(explode(' ', $name ?? 'S'))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)->join('');
    }
@endphp

{{-- ── HEADER ── --}}
<div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Publicaciones</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Novedades y comunicados del club</p>
    </div>
    <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 dark:bg-green-900/20 px-3 py-1 text-xs font-medium text-green-700 dark:text-green-400 ring-1 ring-inset ring-green-600/20">
        {{ $noticias->count() }} publicaciones
    </span>
</div>

{{-- ── FEATURED POST ── --}}
@if($featured)
<div class="group relative overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 hover:shadow-md transition-shadow duration-200">
    <div class="flex flex-col md:flex-row">

        {{-- Image --}}
        @if($featured->Imagen)
        <div class="md:w-1/2 lg:w-3/5 flex-shrink-0 overflow-hidden">
            <img src="{{ asset('storage/' . $featured->Imagen) }}"
                 alt="{{ $featured->Titulo }}"
                 class="h-64 md:h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        </div>
        @endif

        {{-- Content --}}
        <div class="flex flex-col justify-between p-7 {{ $featured->Imagen ? 'md:w-1/2 lg:w-2/5' : 'w-full' }}">
            <div>
                <span class="inline-block rounded-full bg-green-100 dark:bg-green-900/30 px-3 py-0.5 text-xs font-semibold text-green-700 dark:text-green-400 mb-4">
                    Destacado
                </span>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white leading-snug mb-2">
                    {{ $featured->Titulo }}
                </h2>
                @if($featured->Subtitulo)
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">{{ $featured->Subtitulo }}</p>
                @endif
                <div class="prose prose-sm dark:prose-invert text-gray-600 dark:text-gray-300">
                    {!! $featured->Contenido !!}
                </div>
            </div>

            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white text-xs font-bold flex-shrink-0">
                        {{ noticia_initials($featured->user?->name ?? 'Sistema') }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $featured->user?->name ?? 'Sistema' }}</p>
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($featured->created_at)->translatedFormat('j \d\e F, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ── GRID ── --}}
@if($rest->count())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($rest as $noticia)
    @php
        $initials = noticia_initials($noticia->user?->name ?? 'Sistema');
    @endphp
    <article class="group flex flex-col overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 hover:shadow-md transition-shadow duration-200">

        {{-- Image --}}
        @if($noticia->Imagen)
        <div class="overflow-hidden h-48 flex-shrink-0">
            <img src="{{ asset('storage/' . $noticia->Imagen) }}"
                 alt="{{ $noticia->Titulo }}"
                 class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        </div>
        @else
        <div class="h-3 bg-gradient-to-r from-green-600 to-green-400 flex-shrink-0"></div>
        @endif

        {{-- Body --}}
        <div class="flex flex-col flex-1 p-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white line-clamp-2 mb-1 leading-snug">
                {{ $noticia->Titulo }}
            </h3>
            @if($noticia->Subtitulo)
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-3">{{ $noticia->Subtitulo }}</p>
            @endif
            <div class="prose prose-sm dark:prose-invert text-gray-600 dark:text-gray-300 text-sm flex-1">
                {!! $noticia->Contenido !!}
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center gap-3 px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-green-600 text-white text-[10px] font-bold flex-shrink-0">
                {{ $initials }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate">{{ $noticia->user?->name ?? 'Sistema' }}</p>
                <p class="text-[11px] text-gray-400">{{ \Carbon\Carbon::parse($noticia->created_at)->translatedFormat('j M Y') }}</p>
            </div>
        </div>
    </article>
    @endforeach
</div>
@endif

{{-- ── EMPTY STATE ── --}}
@if($items->isEmpty())
<div class="flex flex-col items-center justify-center py-20 text-center">
    <div class="rounded-full bg-gray-100 dark:bg-gray-700 p-6 mb-4">
        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
        </svg>
    </div>
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Sin publicaciones</h3>
    <p class="text-sm text-gray-500 mt-1">Aún no hay publicaciones disponibles.</p>
</div>
@endif

{{-- ── LOAD MORE ── --}}
@if($noticias->hasMorePages() || $noticias->count() >= $perPage)
<div class="flex justify-center pt-2">
    <button wire:click="nextPage"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
        <svg wire:loading.remove wire:target="nextPage" class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
        <svg wire:loading wire:target="nextPage" class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        Cargar más publicaciones
    </button>
</div>
@endif

</div>
