@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-center">
        <ul class="flex list-none p-0 m-0 items-center justify-center gap-1">
            
            @if (!$paginator->onFirstPage())
                {{-- Primera página << --}}
                <li class="flechas-home lg:h-10 lg:text-xl h-7 text-xs">
                    <a href="{{ $paginator->url(1) }}" class="h-full w-full flex items-center justify-center">&lt;&lt;</a>
                </li>

                {{-- Página Anterior < --}}
                <li class="flechas-home lg:h-10 lg:text-xl h-7 text-xs">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="h-full w-full flex items-center justify-center">&lt;</a>
                </li>
            @endif

            {{-- Cálculo de ventana fija de 5 números (Se mantiene igual) --}}
            @php
                $inicio = max($paginator->currentPage() - 2, 1);
                $fin = min($inicio + 4, $paginator->lastPage());
                
                if ($fin - $inicio < 4) {
                    $inicio = max($fin - 4, 1);
                }
            @endphp

            @for ($i = $inicio; $i <= $fin; $i++)
                @if ($i == $paginator->currentPage())
                    <li class="flechas-home flechas-home-focus lg:h-10 lg:text-xl h-7 text-xs" aria-current="page">
                        <span class="h-full w-full flex items-center justify-center">{{ $i }}</span>
                    </li>
                @else
                    <li class="flechas-home lg:h-10 lg:text-xl h-7 text-xs">
                        <a href="{{ $paginator->url($i) }}" class="h-full w-full flex items-center justify-center">{{ $i }}</a>
                    </li>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                {{-- Página Siguiente > --}}
                <li class="flechas-home lg:h-10 lg:text-xl h-7 text-xs">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="h-full w-full flex items-center justify-center">&gt;</a>
                </li>

                {{-- Última página >> --}}
                <li class="flechas-home lg:h-10 lg:text-xl h-7 text-xs">
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="h-full w-full flex items-center justify-center">&gt;&gt;</a>
                </li>
            @endif

        </ul>
    </nav>
@endif