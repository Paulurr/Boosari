@props([
    'tipo' => 'Tipo de Registro',
    'titulo' => 'Subtitulo',
    'icono' => null,
    'monto' => '0.00',
    'origen' => null,
    'destino' => null,
    'categoria' => null,
    'fecha' => '12/09/2026'
])

<div class="bgcol1 mb-5 mr-2 ml-2 md:mb-0 h-full lg:w-1/4 w-[60%] rounded-md border col3 transition-all flex flex-col justify-between">
    
    {{-- Cabecera --}}
    <div class="h-1/6 w-full flex">
        <div class="p-3 w-full">
            <h1 class="sm:text-xs text-[10px] font-bold col7 uppercase tracking-wider">
                {{ $tipo }}
            </h1>
            <h2 class="lg:text-md text-[10px] col7 truncate font-medium">
                {{ $titulo }}
            </h2>
        </div>
        <div class="w-full sm:text-[12px] text-[8px] text-end p-3 col7">
            <div class="editar-btn cursor-pointer">
                Editar
            </div>
        </div>
    </div>

    {{-- Imagen / Icono --}}
    <div class="w-full overflow-hidden relative h-3/6 bgcol1 flex items-center justify-center min-h-30">
        @if($icono)
            <img src="{{ asset('storage/' . $icono) }}" alt="Icono" class="absolute h-full object-contain p-2">
        @else
            <img src="{{ asset('images/logo_boosari.webp') }}" alt="Default" class="absolute h-full object-fill">
        @endif
    </div>

    {{-- Cuerpo de Datos --}}
    <div class="w-full h-2/6 p-3 flex flex-col justify-between">
        <div>
            <p class="sm:text-[14px] text-[12px] font-bold col7">
                Monto: ${{ $monto }}
            </p>
            
            {{-- Mapeos Limpios y Condicionales --}}
            @if($origen)
                <p class="sm:text-[12px] text-[10px] col7 truncate">
                    {{-- Detecta automáticamente si es texto informativo o una relación --}}
                    <span class="font-semibold">{{ str_contains($origen, ':') ? '' : 'Origen:' }}</span> {{ $origen }}
                </p>
            @endif

            @if($destino)
                <p class="sm:text-[12px] text-[10px] col7 truncate">
                    <span class="font-semibold">{{ str_contains($destino, ':') ? '' : 'Destino:' }}</span> {{ $destino }}
                </p>
            @endif

            @if($categoria)
                <p class="sm:text-[12px] text-[10px] col7 truncate">
                    <span class="font-semibold">Categoria:</span> {{ $categoria }}
                </p>
            @endif
        </div>

        {{-- Fecha inferior --}}
        <p class="text-[10px] font-light text-end col7 mt-2">
            {{ $fecha }}
        </p>
    </div>
</div>