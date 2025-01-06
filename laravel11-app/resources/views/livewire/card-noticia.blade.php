<div class="flex flex-col">
    <div
        class="mx-0 mt-6 rounded-md text-surface bg-gray-100 shadow-lg border border-gray-500 md:mx-3 dark:!border-gray-800 dark:bg-gray-800 bg-opacity-90 relative overflow-hidden h-full">

{{--        Objeto Ribbon--}}
        {{--<div class="absolute left-0 top-0 h-16 w-16 opacity-75">
                <div
                    class="bg-primary-600 absolute transform -rotate-45 text-center text-white font-semibold py-1 left-[-34px] top-[32px] w-[170px]">
                    {{$noticia->Estado}}
                </div>
        </div>--}}

{{--        Imagen de noticia--}}
        <div>
            @if($noticia->Imagen)
                <img class="rounded rounded-t"
                     src="{{asset("/storage/".$noticia->Imagen)}}"/>
            @endif
        </div>

{{--        Objeto Autor--}}
        <div class="px-3 pt-4">
            <div class="flex items-center">
                {{--<img class="w-10 h-10 rounded-full mr-4"
                     src="'/images/iconos/Ability_bage_36.png'">--}}

            </div>
        </div>

{{--        Contenido de noticia--}}
        <div class="p-6">
            <h5 class="mb-2 text-xl font-medium leading-tight">{{$noticia->Titulo}}</h5>
            <small class="mb-2 leading-tight">{{$noticia->Subtitulo}}</small>

            <p class="pt-4 mb-4 text-base">
                {!! $noticia->Contenido !!}
            </p>
        </div>

{{--        Creado por--}}
        <div
            class="mt-auto border-t-2 border-neutral-100 px-6 py-3 text-right text-surface/75 dark:border-white/10 dark:text-neutral-300">
            <div class="text-sm text-left">
                <small class="text-dark leading-none"> Por : {{$noticia->user->name ?? 'Sistema'}}</small>
            </div>
            <small>{{\Carbon\Carbon::parse($noticia->created_at)->format("d/m/Y")}}</small>
        </div>
    </div>
</div>
