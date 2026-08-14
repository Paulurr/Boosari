@props([
    'id' => null,
    'name' => '',
    'tipo' => 'Tipo de Registro',
    'titulo' => 'Subtitulo',
    'icono' => null,
    'monto' => '0.00',
    'origen' => null,
    'destino' => null,
    'categoria' => null,
    'fecha' => '12/09/2026'
])

<div id="record-card-{{ $name }}-{{ $id }}" class="bgcol1 mb-5 mr-2 ml-2 md:mb-0 h-full lg:w-1/4 w-[60%] rounded-md border col3 transition-all flex flex-col justify-between">
    
    {{-- Cabecera --}}
    <div class="h-1/6 w-full flex">
        <div class="p-3 w-full">
            <h1 class="sm:text-xs text-[10px] font-bold col7 uppercase tracking-wider">
                {{ $tipo }}
            </h1>
            <h2 id="record-card-{{ $name }}-{{ $id }}-titulo" class="lg:text-md text-[10px] col7 truncate font-medium">
                {{ $titulo }}
            </h2>
        </div>
        <div class="w-full sm:text-[12px] text-[8px] text-end p-3 col7">
            <div class="{{ $name }}-btn editar-btn cursor-pointer"
            data-id="{{ $id }}" >
                Detalles
            </div>
        </div>
    </div>

    {{-- Imagen / Icono --}}
    <div class="w-full overflow-hidden relative h-3/6 bgcol1 flex items-center justify-center min-h-30">
        @if($icono)
            <img id="record-card-{{ $name }}-{{ $id }}-icono" src="{{ asset('storage/' . $icono) }}" alt="Icono" class="absolute h-full object-contain p-2">
        @else
            <img id="record-card-{{ $name }}-{{ $id }}-icono" src="{{ asset('images/logo_boosari.webp') }}" alt="Default" class="absolute h-full object-fill">
        @endif
    </div>

    {{-- Cuerpo de Datos --}}
    <div class="w-full h-2/6 p-3 flex flex-col justify-between">
        <div>
            <p id="record-card-{{ $name }}-{{ $id }}-monto" class="sm:text-[14px] text-[12px] font-bold col7">
                Monto: ${{ $monto }}
            </p>
            
            <p id="record-card-{{ $name }}-{{ $id }}-origen" class="sm:text-[12px] text-[10px] col7 truncate {{ $origen ? '' : 'hidden' }}">
                <span class="font-semibold"></span> {{ $origen }}
            </p>

            <p id="record-card-{{ $name }}-{{ $id }}-destino" class="sm:text-[12px] text-[10px] col7 truncate {{ $destino ? '' : 'hidden' }}">
                <span class="font-semibold"></span> {{ $destino }}
            </p>

            <p id="record-card-{{ $name }}-{{ $id }}-categoria" class="sm:text-[12px] text-[10px] col7 truncate {{ $categoria ? '' : 'hidden' }}">
                <span class="font-semibold"></span> {{ $categoria }}
            </p>
        </div>

        {{-- Fecha inferior --}}
        <p id="record-card-{{ $name }}-{{ $id }}-fecha" class="text-[10px] font-light text-end col7 mt-2">
            {{ $fecha }}
        </p>
    </div>
</div>