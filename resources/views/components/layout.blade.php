<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
 
        {{-- Favicon: un solo lugar, con el archivo real que sí existe --}}
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
 
        <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
 
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        {{-- Paleta personalizada del usuario (si existe), para que ThemeMode.js
             la use en vez de los colores base. Debe ir ANTES de cargar ThemeMode.js
             (que se importa desde nav.blade.php más abajo, dentro de $slot). --}}
        @auth
            <script>
                window.PALETA_PERSONALIZADA = @json(
                    collect(auth()->user()->configuracion?->coloresClaroConFallback() ?? [])
                        ->mapWithKeys(fn($hex, $indice) => ["--col{$indice}" => $hex])
                );
                window.PALETA_PERSONALIZADA_OSCURA = @json(
                    collect(auth()->user()->configuracion?->coloresOscuroConFallback() ?? [])
                        ->mapWithKeys(fn($hex, $indice) => ["--col{$indice}" => $hex])
                );
            </script>
        @endauth
 
        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
 
        {{-- Slot opcional para scripts extra por página (chart.js, agent.js, etc.) --}}
        {{ $head ?? '' }}
    </head>
    <body class="{{ $bodyClass ?? 'bgcol2' }}">
        {{ $slot }}
    </body>
</html>