<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Registros</title>
    <link rel="stylesheet" href="{{ str_replace('\\', '/', public_path('css/pdf-reportes.css')) }}">
</head>
<body class="pdf-body">
    <div class="pdf-title">Reporte de Registros</div>
    <div class="subtitle">Generado el {{ $generadoEl }}</div>
    <div class="meta">
        @if(!empty($filtros['busqueda']))
            Búsqueda: "{{ $filtros['busqueda'] }}" &nbsp;|&nbsp;
        @endif
        Orden: {{ $filtros['orden'] }} &nbsp;|&nbsp;
        Total de registros: {{ count($registros) }}
    </div>

    <table class="totales">
        <tr>
            <td width="50%">
                <div class="card">
                    <div class="label">Total general</div>
                    <div class="value">${{ number_format($totalGeneral, 2) }}</div>
                </div>
            </td>
            <td width="50%">
                <div class="card">
                    <div class="label">Cantidad de registros</div>
                    <div class="value">{{ count($registros) }}</div>
                </div>
            </td>
        </tr>
    </table>

    @if($chartTipoImg || $chartCategoriaImg)
    <table class="charts">
        <tr>
            <td>
                @if($chartTipoImg)
                    <img src="{{ $chartTipoImg }}" alt="Por tipo">
                @endif
            </td>
            <td>
                @if($chartCategoriaImg)
                    <img src="{{ $chartCategoriaImg }}" alt="Por categoría">
                @endif
            </td>
        </tr>
    </table>
    @endif

    <table class="resumen-tabla">
        <thead>
            <tr>
                <th>Tipo de registro</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($totalPorTipo as $tipo => $total)
                <tr>
                    <td>{{ $tipo }}</td>
                    <td class="text-right">${{ number_format($total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Título</th>
                <th>Categoría</th>
                <th class="text-right">Monto</th>
                <th>Info extra</th>
                <th>Billetera</th>
            </tr>
        </thead>
        <tbody>
            @foreach($registros as $r)
                <tr>
                    <td>{{ $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : '' }}</td>
                    <td>{{ $tipoLabels[$r->tipo_registro] ?? $r->tipo_registro }}</td>
                    <td>{{ $r->titulo }}</td>
                    <td>{{ $r->categoria ?? 'Sin categoría' }}</td>
                    <td class="text-right">${{ number_format((float) $r->monto, 2) }}</td>
                    <td>{{ $r->extra_info }}</td>
                    <td>{{ $r->billetera_destino ?? $r->billetera_origen }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>