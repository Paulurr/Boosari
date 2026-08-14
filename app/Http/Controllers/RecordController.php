<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Income;
use App\Models\Transaction;
use App\Models\Investment;
use App\Models\Goal;
use App\Models\PaymentGoal;
use App\Models\Debt;
use App\Models\PaymentDebt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Barryvdh\DomPDF\Facade\Pdf;
class RecordController extends Controller
{
    private function buildFilteredRecordsQuery(Request $request)
    {
        $userId = Auth::id();

        $sortBy = $request->input('sort_by', 'date_desc');
        $rangeAmount = $request->input('range_amount') ? (array) $request->input('range_amount') : [];
        $timeFrame = $request->input('time_frame') ? (array) $request->input('time_frame') : [];
        $recordType = $request->input('record_type') ? (array) $request->input('record_type') : [];
        $currentCategory = $request->input('category_id') ? (array) $request->input('category_id') : [];
        $search = trim((string) $request->input('search', ''));

        // 2. Mapeos de subconsultas estandarizadas
        $queries = [];

        $subqueriesConfig = [
            'wallet' => [
                'table' => 'wallets',
                'monto_col' => 'wallets.monto_actual',
                'monto_inicial' => 'wallets.monto_inicial',
                'extra_info' => 'wallets.tipo',
                'fecha' => 'wallets.created_at',
                'join_category' => false,
                'join_wallet' => false,
                'join_wallet_origen' => false
            ],
            'debt' => [
                'table' => 'debts',
                'monto_col' => 'debts.monto_actual',
                'monto_inicial' => 'debts.monto_inicial',
                'extra_info' => 'debts.prioridad',
                'fecha' => 'debts.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'debts.fecha_vencimiento'
            ],
            'goal' => [
                'table' => 'goals',
                'monto_col' => 'goals.monto_objetivo',
                'monto_inicial' => 'goals.monto_inicial',
                'extra_info' => 'goals.estado',
                'fecha' => 'goals.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'goals.fecha_limite' 
            ],
            'income' => [
                'table' => 'incomes',
                'monto_col' => 'incomes.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => 'incomes.frecuencia',
                'fecha' => 'COALESCE(incomes.fecha_inicio, incomes.created_at)',
                'join_category' => true,
                'join_wallet' => true,
                'join_wallet_origen' => false,
                'fk_wallet_destino' => 'incomes.wallet_id'
            ],
            'investment' => [
                'table' => 'investments',
                'monto_col' => 'investments.valor_actual',
                'monto_inicial' => 'investments.monto_inicial',
                'extra_info' => 'investments.tipo_renta',
                'fecha' => 'investments.fecha_adquisicion',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'fecha_vencimiento_raw' => 'investments.fecha_vencimiento', 
                'tasa_interes_raw' => 'investments.tasa_interes'
            ],
            'transaction' => [
                'table' => 'transactions',
                'monto_col' => 'transactions.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => 'transactions.tipo',
                'fecha' => 'COALESCE(transactions.fecha_ejecucion, transactions.created_at)',
                'join_category' => true,
                'join_wallet' => true,
                'join_wallet_origen' => true,
                'fk_wallet_destino' => 'transactions.wallet_destino_id', 
                'fk_wallet_origen'  => 'transactions.wallet_origen_id'
            ],
            'paymentGoal' => [
                'table' => 'payment_goals',
                'monto_col' => 'payment_goals.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => "''",
                'fecha' => 'payment_goals.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'join_parent' => ['table' => 'goals', 'foreign_key' => 'payment_goals.goal_id', 'owner_col' => 'goals.user_id'],
                'join_parent_title' => true
            ],
            'paymentDebt' => [
                'table' => 'payment_debts',
                'monto_col' => 'payment_debts.monto',
                'monto_inicial' => 'NULL',
                'extra_info' => "''",
                'fecha' => 'payment_debts.created_at',
                'join_category' => true,
                'join_wallet' => false,
                'join_wallet_origen' => false,
                'join_parent' => ['table' => 'debts', 'foreign_key' => 'payment_debts.debt_id', 'owner_col' => 'debts.user_id'],
                'join_parent_title' => true
            ],
        ];

        // Procesamiento dinámico para el UNION
        foreach ($subqueriesConfig as $tipo => $config) {
            // CORRECCIÓN: Si el array de tipos no está vacío y el tipo actual NO está seleccionado, lo omitimos
            if (!empty($recordType) && !in_array($tipo, $recordType)) {
                continue;
            }

            $query = DB::table($config['table']);

            if (isset($config['join_parent'])) {
                $query->join($config['join_parent']['table'], $config['join_parent']['foreign_key'], '=', $config['join_parent']['table'] . '.id');
                $userColumn = $config['join_parent']['owner_col'];
            } else {
                $userColumn = $config['table'] . '.user_id';
            }

            if ($config['join_category']) {
                $query->leftJoin('categories', $config['table'] . '.category_id', '=', 'categories.id');
            }

            if ($config['join_wallet']) {
                $fkDestino = $config['fk_wallet_destino'] ?? ($config['table'] . '.wallet_id');
                $query->leftJoin('wallets AS w_destino', $fkDestino, '=', 'w_destino.id');
            }

            if ($config['join_wallet_origen']) {
                $fkOrigen = $config['fk_wallet_origen'] ?? ($config['table'] . '.wallet_origen_id');
                $query->leftJoin('wallets AS w_origen', $fkOrigen, '=', 'w_origen.id');
            }
            $query->select([
                $config['table'] . '.id AS id', // Asegura el ID del registro
                $config['table'] . '.titulo AS titulo',
                $config['table'] . '.icono AS icono',
                DB::raw("$userColumn AS user_id"),
                
                // SOLUCIÓN CLAVE: Fuerza el alias exacto para mapear en el UNION
                $config['table'] === 'wallets' 
                    ? DB::raw("NULL AS category_id") 
                    : $config['table'] . '.category_id AS category_id',
                
                DB::raw($config['monto_col'] . " AS monto"),
                DB::raw($config['monto_inicial'] . " AS monto_inicial"),
                DB::raw($config['extra_info'] . " AS extra_info"),
                DB::raw("'$tipo' AS tipo_registro"),
                DB::raw($config['fecha'] . " AS fecha"),
                
                isset($config['fecha_vencimiento_raw']) ? DB::raw($config['fecha_vencimiento_raw'] . ' AS vencimiento_registro') : DB::raw("NULL AS vencimiento_registro"),
                isset($config['tasa_interes_raw']) ? DB::raw($config['tasa_interes_raw'] . ' AS tasa_interes_registro') : DB::raw("NULL AS tasa_interes_registro"),
                
                (isset($config['join_parent_title']) && $config['join_parent_title']) 
                    ? $config['join_parent']['table'] . '.titulo AS nombre_padre' 
                    : DB::raw("NULL AS nombre_padre"),
                
                $config['join_category'] ? 'categories.categoria AS categoria' : DB::raw("NULL AS categoria"),
                $config['join_wallet'] ? 'w_destino.titulo AS billetera_destino' : DB::raw("NULL AS billetera_destino"),
                $config['join_wallet_origen'] ? 'w_origen.titulo AS billetera_origen' : DB::raw("NULL AS billetera_origen"),
            ])->where($userColumn, $userId);

            $queries[] = $query;
        } 
        // =========================================================================
        // 3 y 4. Unificar mediante UNION todas las subconsultas de forma Nativa
        // =========================================================================
        $firstQuery = array_shift($queries);
        
        if (!$firstQuery) {
            // Consulta vacía de respaldo si no hay tipos seleccionados
            $firstQuery = DB::table('transactions')
                ->select([
                    'id', 'titulo', 'icono', 'user_id', 'category_id', 
                    'monto', 'monto_inicial', 'extra_info', 'tipo_registro', 'fecha',
                    'vencimiento_registro', 'tasa_interes_registro', 'nombre_padre',
                    'categoria', 'billetera_destino', 'billetera_origen'
                ])
                ->whereRaw('1 = 0');
        }

        // Unificar el resto de consultas de manera limpia
        foreach ($queries as $subQuery) {
            $firstQuery->unionAll($subQuery);
        }

        // CREAR LA SUBCONSULTA PROTEGIENDO LOS BINDINGS NATIVAMENTE
        // Al pasar directamente la instancia de la query al FROM, Laravel gestiona los bindings en orden perfecto.
        $mainQuery = DB::table($firstQuery, 'registros');

        // =========================================================================
        // 5. Aplicación de Filtros Globales Múltiples (Corregido con Aislamiento Estricto)
        // =========================================================================

        // CATEGORÍAS MÚLTIPLES: Forzado en un subgrupo condicional aislado
        if (!empty($currentCategory)) {
            $mainQuery->where(function($q) use ($currentCategory) {
                foreach ($currentCategory as $index => $catId) {
                    if ($index === 0) {
                        $q->where('category_id', '=', $catId);
                    } else {
                        $q->orWhere('category_id', '=', $catId);
                    }
                }
            });
        }

        // RANGOS DE DINERO MÚLTIPLES
        if (!empty($rangeAmount)) {
            $mainQuery->where(function($q) use ($rangeAmount) {
                if (in_array('low', $rangeAmount)) {
                    $q->orWhere('monto', '<', 50);
                }
                if (in_array('medium', $rangeAmount)) {
                    $q->orWhereBetween('monto', [50, 500]);
                }
                if (in_array('high', $rangeAmount)) {
                    $q->orWhere('monto', '>', 500);
                }
            });
        }

        // PERÍODO DE TIEMPO MÚLTIPLE (antes se capturaba pero nunca se aplicaba a la consulta)
        if (!empty($timeFrame)) {
            $mainQuery->where(function($q) use ($timeFrame) {
                if (in_array('today', $timeFrame)) {
                    $q->orWhereDate('fecha', now()->toDateString());
                }
                if (in_array('week', $timeFrame)) {
                    $q->orWhereBetween('fecha', [now()->startOfWeek(), now()->endOfWeek()]);
                }
                if (in_array('month', $timeFrame)) {
                    $q->orWhereBetween('fecha', [now()->startOfMonth(), now()->endOfMonth()]);
                }
                if (in_array('year', $timeFrame)) {
                    $q->orWhereBetween('fecha', [now()->startOfYear(), now()->endOfYear()]);
                }
            });
        }

        // BÚSQUEDA POR TEXTO: título del registro, categoría, info extra (tipo/estado/etc.) o nombre del registro padre (meta/deuda)
        if ($search !== '') {
            $mainQuery->where(function($q) use ($search) {
                $like = '%' . $search . '%';
                $q->where('titulo', 'like', $like)
                  ->orWhere('categoria', 'like', $like)
                  ->orWhere('extra_info', 'like', $like)
                  ->orWhere('nombre_padre', 'like', $like);
            });
        }
        // 6. Aplicación de Ordenamiento
        if ($sortBy === 'amount_desc') {
            $mainQuery->orderBy('monto', 'desc');
        } elseif ($sortBy === 'alpha_asc') {
            $mainQuery->orderBy('titulo', 'asc');
        } else {
            $mainQuery->orderBy('fecha', 'desc');
        }

        return $mainQuery;
    }

    /**
     * =====================================================================
     *  EXPORTAR A EXCEL (.xlsx) — respeta filtros, orden y búsqueda actuales
     *  Genera 2 hojas: "Registros" (detalle) y "Resumen" (totales + gráficas
     *  nativas de Excel: pastel por tipo y barras por categoría).
     * =====================================================================
     */
    public function exportExcel(Request $request)
    {
        $registros = $this->buildFilteredRecordsQuery($request)->get();

        $tipoLabels = [
            'wallet' => 'Billetera', 'debt' => 'Deuda', 'goal' => 'Meta', 'income' => 'Ingreso',
            'investment' => 'Inversión', 'transaction' => 'Transacción',
            'paymentGoal' => 'Pago de meta', 'paymentDebt' => 'Pago de deuda',
        ];

        $spreadsheet = new Spreadsheet();

        // ================= HOJA 1: REGISTROS =================
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Registros');

        $headers = ['Fecha', 'Tipo', 'Título', 'Categoría', 'Monto', 'Monto Inicial', 'Info Extra', 'Billetera Origen', 'Billetera Destino', 'Registro Relacionado', 'Vencimiento'];
        $sheet->fromArray($headers, null, 'A1');

        $headerRange = 'A1:' . chr(64 + count($headers)) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 2;
        $totalPorTipo = [];
        $totalPorCategoria = [];

        foreach ($registros as $r) {
            $tipoLabel = $tipoLabels[$r->tipo_registro] ?? $r->tipo_registro;
            $categoria = $r->categoria ?? 'Sin categoría';

            $sheet->setCellValue("A{$row}", $r->fecha ? Carbon::parse($r->fecha)->format('d/m/Y H:i') : '');
            $sheet->setCellValue("B{$row}", $tipoLabel);
            $sheet->setCellValue("C{$row}", $r->titulo);
            $sheet->setCellValue("D{$row}", $categoria);
            $sheet->setCellValue("E{$row}", (float) $r->monto);
            $sheet->setCellValue("F{$row}", $r->monto_inicial !== null ? (float) $r->monto_inicial : null);
            $sheet->setCellValue("G{$row}", $r->extra_info);
            $sheet->setCellValue("H{$row}", $r->billetera_origen);
            $sheet->setCellValue("I{$row}", $r->billetera_destino);
            $sheet->setCellValue("J{$row}", $r->nombre_padre);
            $sheet->setCellValue("K{$row}", $r->vencimiento_registro ? Carbon::parse($r->vencimiento_registro)->format('d/m/Y') : '');

            $totalPorTipo[$tipoLabel] = ($totalPorTipo[$tipoLabel] ?? 0) + (float) $r->monto;
            $totalPorCategoria[$categoria] = ($totalPorCategoria[$categoria] ?? 0) + (float) $r->monto;

            $row++;
        }
        $lastRow = $row - 1;

        if ($lastRow >= 2) {
            $sheet->getStyle("E2:F{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("A1:K{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        // ================= HOJA 2: RESUMEN + GRÁFICAS =================
        $resumen = $spreadsheet->createSheet();
        $resumen->setTitle('Resumen');

        $resumen->setCellValue('A1', 'Total por tipo de registro');
        $resumen->getStyle('A1')->getFont()->setBold(true);
        $resumen->fromArray(['Tipo', 'Total'], null, 'A2');
        $resumen->getStyle('A2:B2')->getFont()->setBold(true);
        $r = 3;
        foreach ($totalPorTipo as $tipo => $total) {
            $resumen->setCellValue("A{$r}", $tipo);
            $resumen->setCellValue("B{$r}", round($total, 2));
            $r++;
        }
        $tipoTableEnd = $r - 1;

        $catStart = $r + 2;
        $resumen->setCellValue("A{$catStart}", 'Total por categoría');
        $resumen->getStyle("A{$catStart}")->getFont()->setBold(true);
        $resumen->setCellValue('A' . ($catStart + 1), 'Categoría');
        $resumen->setCellValue('B' . ($catStart + 1), 'Total');
        $resumen->getStyle('A' . ($catStart + 1) . ':B' . ($catStart + 1))->getFont()->setBold(true);
        arsort($totalPorCategoria);
        $r = $catStart + 2;
        foreach ($totalPorCategoria as $categoria => $total) {
            $resumen->setCellValue("A{$r}", $categoria);
            $resumen->setCellValue("B{$r}", round($total, 2));
            $r++;
        }
        $catTableEnd = $r - 1;

        $resumen->getColumnDimension('A')->setAutoSize(true);
        $resumen->getColumnDimension('B')->setAutoSize(true);

        // --- Gráfica de pastel: distribución por tipo ---
        if ($tipoTableEnd >= 3) {
            $count = $tipoTableEnd - 2;
            $labels = [new DataSeriesValues('String', "Resumen!\$A\$3:\$A\${$tipoTableEnd}", null, $count)];
            $values = [new DataSeriesValues('Number', "Resumen!\$B\$3:\$B\${$tipoTableEnd}", null, $count)];
            $categoryLabels = [new DataSeriesValues('String', "Resumen!\$A\$3:\$A\${$tipoTableEnd}", null, $count)];

            $series = new DataSeries(DataSeries::TYPE_PIECHART, null, range(0, count($values) - 1), $labels, $categoryLabels, $values);
            $plotArea = new PlotArea(null, [$series]);
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
            $chart1 = new Chart('grafica_tipos', new Title('Distribución por tipo de registro'), $legend, $plotArea);
            $chart1->setTopLeftPosition('D2');
            $chart1->setBottomRightPosition('L20');
            $resumen->addChart($chart1);
        }

        // --- Gráfica de barras: total por categoría (top 10) ---
        if ($catTableEnd >= $catStart + 2) {
            $catEndCapped = min($catTableEnd, $catStart + 11); // top 10
            $count = $catEndCapped - ($catStart + 2) + 1;
            $labels = [new DataSeriesValues('String', "Resumen!\$A\$" . ($catStart + 2) . ":\$A\${$catEndCapped}", null, $count)];
            $values = [new DataSeriesValues('Number', "Resumen!\$B\$" . ($catStart + 2) . ":\$B\${$catEndCapped}", null, $count)];
            $categoryLabels = [new DataSeriesValues('String', "Resumen!\$A\$" . ($catStart + 2) . ":\$A\${$catEndCapped}", null, $count)];

            $series2 = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, range(0, count($values) - 1), $labels, $categoryLabels, $values);
            $series2->setPlotDirection(DataSeries::DIRECTION_COL);
            $plotArea2 = new PlotArea(null, [$series2]);
            $legend2 = new Legend(Legend::POSITION_RIGHT, null, false);
            $chart2 = new Chart('grafica_categorias', new Title('Top categorías por monto'), $legend2, $plotArea2);
            $chart2->setTopLeftPosition('D22');
            $chart2->setBottomRightPosition('L40');
            $resumen->addChart($chart2);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'registros_' . now()->format('Y-m-d_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * =====================================================================
     *  EXPORTAR A PDF — respeta filtros, orden y búsqueda actuales.
     *  Incluye resumen, totales y gráficas (pastel + barras) generadas con
     *  QuickChart.io e incrustadas como imagen base64 (Dompdf no ejecuta
     *  JS/canvas, así que no puede dibujar Chart.js directamente).
     * =====================================================================
     */
    public function exportPdf(Request $request)
    {
        $registros = $this->buildFilteredRecordsQuery($request)->get();

        $tipoLabels = [
            'wallet' => 'Billetera', 'debt' => 'Deuda', 'goal' => 'Meta', 'income' => 'Ingreso',
            'investment' => 'Inversión', 'transaction' => 'Transacción',
            'paymentGoal' => 'Pago de meta', 'paymentDebt' => 'Pago de deuda',
        ];

        $totalPorTipo = [];
        $totalPorCategoria = [];
        $totalGeneral = 0;

        foreach ($registros as $r) {
            $tipoLabel = $tipoLabels[$r->tipo_registro] ?? $r->tipo_registro;
            $categoria = $r->categoria ?? 'Sin categoría';
            $totalPorTipo[$tipoLabel] = ($totalPorTipo[$tipoLabel] ?? 0) + (float) $r->monto;
            $totalPorCategoria[$categoria] = ($totalPorCategoria[$categoria] ?? 0) + (float) $r->monto;
            $totalGeneral += (float) $r->monto;
        }
        arsort($totalPorCategoria);
        $topCategorias = array_slice($totalPorCategoria, 0, 8, true);

        $chartTipoImg = $this->fetchChartAsBase64(
            $this->buildQuickChartUrl('pie', array_keys($totalPorTipo), array_values($totalPorTipo), 'Por tipo de registro')
        );
        $chartCategoriaImg = $this->fetchChartAsBase64(
            $this->buildQuickChartUrl('bar', array_keys($topCategorias), array_values($topCategorias), 'Top categorías')
        );

        $pdf = Pdf::loadView('exports.records_pdf', [
            'registros' => $registros,
            'tipoLabels' => $tipoLabels,
            'totalPorTipo' => $totalPorTipo,
            'totalPorCategoria' => $totalPorCategoria,
            'totalGeneral' => $totalGeneral,
            'chartTipoImg' => $chartTipoImg,
            'chartCategoriaImg' => $chartCategoriaImg,
            'filtros' => [
                'busqueda' => $request->input('search'),
                'orden' => $request->input('sort_by', 'date_desc'),
            ],
            'generadoEl' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = 'registros_' . now()->format('Y-m-d_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Construye la URL de QuickChart.io (servicio gratuito) para generar
     * la imagen de una gráfica que sí puede incrustarse en un PDF con Dompdf.
     */
    private function buildQuickChartUrl(string $type, array $labels, array $data, string $title): string
    {
        $config = [
            'type' => $type,
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $title,
                    'data' => array_map(fn ($v) => round($v, 2), $data),
                    'backgroundColor' => ['#2F5597', '#8FAADC', '#548235', '#BF8F00', '#C00000', '#7030A0', '#00B0F0', '#ED7D31'],
                ]],
            ],
            'options' => [
                'plugins' => ['title' => ['display' => true, 'text' => $title]],
            ],
        ];

        return 'https://quickchart.io/chart?width=500&height=300&backgroundColor=white&c=' . urlencode(json_encode($config));
    }

    /**
     * Descarga la imagen de la gráfica y la convierte a base64 para
     * incrustarla directamente en el HTML del PDF.
     */
    private function fetchChartAsBase64(string $url): ?string
    {
        try {
            $response = Http::timeout(8)->get($url);
            if ($response->successful()) {
                return 'data:image/png;base64,' . base64_encode($response->body());
            }
        } catch (\Exception $e) {
            // Si falla la generación de la gráfica (p. ej. sin salida a
            // internet), el PDF se genera igual, solo sin esa imagen.
        }

        return null;
    }

    public function index(Request $request)
    {
        $userId = Auth::id();

        // 1. Capturar los filtros (sort_by se queda como string; los demás se fuerzan a arrays si traen datos)
        $sortBy = $request->input('sort_by', 'date_desc');
        $rangeAmount = $request->input('range_amount') ? (array) $request->input('range_amount') : [];
        $timeFrame = $request->input('time_frame') ? (array) $request->input('time_frame') : [];
        $recordType = $request->input('record_type') ? (array) $request->input('record_type') : [];
        $currentCategory = $request->input('category_id') ? (array) $request->input('category_id') : [];
        $search = trim((string) $request->input('search', ''));

        // Variables requeridas por los modales/formularios de tu vista home
        $categories = Category::all();
        $wallets = DB::table('wallets')->where('user_id', $userId)->get();
        $goals = DB::table('goals')->where('user_id', $userId)->get();
        $debts = DB::table('debts')->where('user_id', $userId)->get();

        $mainQuery = $this->buildFilteredRecordsQuery($request);


        // 7. Paginación adaptada
        $records = $mainQuery->paginate(9)->appends($request->all());

        // 8. Si la petición viene por AJAX (barra de búsqueda / filtros), devolvemos
        // la misma vista "home" ya renderizada (con todo lo necesario para que
        // los filtros y la búsqueda queden sincronizados), y el JS se encarga de
        // tomar solo el fragmento de resultados que necesita actualizar.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'html'   => view('home', compact(
                    'records',
                    'categories',
                    'sortBy',
                    'rangeAmount',
                    'timeFrame',
                    'recordType',
                    'currentCategory',
                    'search',
                    'wallets',
                    'goals',
                    'debts'
                ))->render(),
            ]);
        }

        // 9. Retornar vista incluyendo todas las variables necesarias
        return view('home', compact(
            'records',
            'categories',
            'sortBy',
            'rangeAmount',
            'timeFrame',
            'recordType',
            'currentCategory',
            'search',
            'wallets',
            'goals',
            'debts'
        ));
    }


    /**
     * Respuesta de éxito consciente de AJAX.
     * Si la petición viene del panel "add" (fetch con Accept: application/json),
     * responde en JSON para que add_panel.js pueda cerrar el modal y
     * recargar el listado. Si no, conserva el comportamiento clásico de
     * Laravel (redirect + mensaje flash).
     */
    private function successResponse(Request $request, string $message)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Respuesta de error de negocio (no de validación) consciente de AJAX.
     * En JSON se envía bajo la clave "error" para que, al no coincidir con
     * ningún data-error-for de un campo específico, add_panel.js lo pinte
     * en el bloque de error general del formulario correspondiente.
     */
    private function errorResponse(Request $request, string $message, int $status = 422, bool $withInput = false)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'errors'  => ['error' => $message],
            ], $status);
        }

        $redirect = redirect()->back()->withErrors(['error' => $message]);
        return $withInput ? $redirect->withInput() : $redirect;
    }

    public function create_wallet(Request $request)
    {
        $request->validate([
            'wallet-titulo' => 'required|max:25',
            'wallet-monto' => 'required|numeric|min:0|max:999999',
            'wallet-image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            'wallet-select-value' => 'required'
        ]);

        $ruta = null;
        if($request->hasFile('wallet-image')){
            $ruta = $request->file('wallet-image')->store('wallet_icons', 'public');
        }

        Wallet::create([
            'user_id' => Auth::id(),
            'titulo' => $request->input('wallet-titulo'),
            'icono' => $ruta,
            'tipo' => strtolower($request->input('wallet-select-value')),
            'monto_actual' => $request->input('wallet-monto'),
            'monto_inicial' => $request->input('wallet-monto')
        ]);

        return $this->successResponse($request, 'Billetera creada correctamente');
    }

   public function create_transaction(Request $request)
    {
        // 1. Validaciones adaptadas a los nombres reales que viajan desde tus componentes Blade
        $request->validate([
            'transaction-titulo'                       => 'required|string|max:25',
            'transaction-category'                     => 'required|string|max:25',
            'transaction-monto'                        => 'required|numeric|min:0.01',
            'transaction-select-value'                 => 'required|string|in:Ingreso,Gasto,Transferencia',
            'transaction-destino-ingreso-select-value' => 'nullable|string', // Se usa para Ingreso
            'transaction-destino-gasto-select-value'   => 'nullable|string', // Se usa para Gasto
            'transaction-origen-select-value'          => 'nullable|string', // Se usa para Transferencia (Origen)
            'transaction-destino-select-value'         => 'nullable|string', // Se usa para Transferencia (Destino)
            'transaction-image'                        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096'
        ]);

        DB::beginTransaction();

        try {
            $ruta = null;
            if ($request->hasFile('transaction-image')) {
                $ruta = $request->file('transaction-image')->store('transactions', 'public');
            }

            $monto = $request->input('transaction-monto');
            $tipo = strtolower($request->input('transaction-select-value'));

            // Inicializar variables de billeteras
            $walletOrigenId = null;
            $walletDestinoId = null;

            // 2. Mapeo inteligente según la naturaleza dinámica de los bloques del Front-end
            if ($tipo === 'ingreso') {
                $walletDestinoId = $request->input('transaction-destino-ingreso-select-value');
            } elseif ($tipo === 'gasto') {
                // En los gastos, la cuenta de donde sale el dinero actúa técnicamente como destino/pago
                $walletDestinoId = $request->input('transaction-destino-gasto-select-value');
            } elseif ($tipo === 'transferencia') {
                $walletOrigenId = $request->input('transaction-origen-select-value');
                $walletDestinoId = $request->input('transaction-destino-select-value');
                
                if ($walletOrigenId === 'Externa') {
                    $walletOrigenId = null;
                }
            }

            // 3. Crear u obtener la categoría del movimiento
            $category = Category::firstOrCreate([
                'user_id'   => Auth::id(),
                'categoria' => trim($request->input('transaction-category'))
            ]);

            // 4. Cargar y verificar las instancias de billetera vinculadas al usuario
            $origen = $walletOrigenId ? Wallet::where('user_id', Auth::id())->lockForUpdate()->findOrFail($walletOrigenId) : null;
            $destino = $walletDestinoId ? Wallet::where('user_id', Auth::id())->lockForUpdate()->findOrFail($walletDestinoId) : null;

            // Validaciones de reglas de negocio financieras
            if (!$destino) {
                throw new \Exception('Debe seleccionar una billetera válida para completar la operación.');
            }

            if ($tipo === 'ingreso' && $destino->tipo === 'credito') {
                DB::rollBack();
                return $this->errorResponse($request, 'Operación inválida: No se pueden registrar ingresos directos a una tarjeta de crédito.');
            }

            if ($tipo === 'transferencia' && $origen && $origen->tipo === 'credito') {
                DB::rollBack();
                return $this->errorResponse($request, 'Operación inválida: No puedes transferir fondos usando una tarjeta de crédito como origen.');
            }

            // 5. Ejecutar operaciones matemáticas de balances
            if ($tipo === 'ingreso') {
                $destino->increment('monto_actual', $monto);

            } elseif ($tipo === 'gasto') {
                if ($destino->tipo === 'credito') {
                    // En tarjetas de crédito los gastos incrementan la deuda global
                    $destino->increment('monto_actual', $monto);
                } else {
                    if ($destino->monto_actual < $monto) {
                        throw new \Exception('Fondos insuficientes en la cuenta elegida.');
                    }
                    $destino->decrement('monto_actual', $monto);
                }

            } elseif ($tipo === 'transferencia') {
                if ($origen) {
                    if ($origen->monto_actual < $monto) {
                        throw new \Exception('Fondos insuficientes en la cuenta origen.');
                    }
                    $origen->decrement('monto_actual', $monto);
                }

                if ($destino->tipo === 'credito') {
                    // Si el destino es crédito, la transferencia mitiga/amortiza la deuda (resta)
                    $destino->decrement('monto_actual', $monto);
                } else {
                    $destino->increment('monto_actual', $monto);
                }
            }

            // 6. Registrar la auditoría del Movimiento
            Transaction::create([
                'user_id'           => Auth::id(),
                'wallet_origen_id'  => $origen ? $origen->id : null,
                'wallet_destino_id' => $destino ? $destino->id : null,
                'category_id'       => $category->id,
                'titulo'            => $request->input('transaction-titulo'),
                'icono'             => $ruta,
                'monto'             => $monto,
                'tipo'              => $tipo,
                'descripcion'       => null
            ]);

            DB::commit();
            return $this->successResponse($request, 'Movimiento procesado de manera correcta.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($request, 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function create_income(Request $request)
    {
        $request->validate([
            'income-titulo' => 'required|max:25',
            'income-category' => 'required|max:25',
            'income-monto' => 'required|numeric|min:0.01',
            'income-wallet-select-value' => 'nullable',
            'income-select-value' => 'required',
            'income-image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
        ]);

        $frecuencia = strtolower(trim($request->input('income-select-value')));
        
        if ($frecuencia != 'ninguno') {
            $request->validate([
                'income-ejecucion' => [
                    'required',
                    'date',
                    'after_or_equal:1970-01-01',
                    'before_or_equal:' . now()->addYears(10)->format('Y-m-d')
                ]
            ]);
        }

        $ruta = null;
        if ($request->hasFile('income-image')) {
            $ruta = $request->file('income-image')->store('income_icons', 'public');
        }

        $category = Category::firstOrCreate([
            'user_id' => Auth::id(),
            'categoria' => trim($request->input('income-category'))
        ]);

        // Buscar billetera por su nombre
        $walletId = null;
        $rawValue = $request->input('income-wallet-select-value');

        // Validamos que venga un ID numérico válido y no la palabra 'Ninguno'
        if ($request->filled('income-wallet-select-value') && strtolower($rawValue) !== 'ninguno') {
            $walletId = intval($rawValue);
        }

        $fechaInicio = $request->filled('income-ejecucion') 
            ? \Carbon\Carbon::parse($request->input('income-ejecucion'))->startOfDay() 
            : now()->startOfDay();

        DB::beginTransaction();

        try {
            $income = Income::create([
                'user_id' => Auth::id(),
                'wallet_id' => $walletId,
                'category_id' => $category->id,
                'titulo' => $request->input('income-titulo'),
                'icono' => $ruta,
                'monto' => $request->input('income-monto'),
                'frecuencia' => $frecuencia,
                'fecha_inicio' => $fechaInicio->format('Y-m-d H:i:s'),
                'activo' => $frecuencia != 'ninguno', 
            ]);

            $hoy = now()->startOfDay();
            $fechaIteracion = $fechaInicio->copy();

            // Límite de seguridad: si la fecha de inicio quedó muy atrás con una
            // frecuencia alta (ej. "diario" desde hace 5 años), este bucle podía
            // generar miles de transacciones en una sola petición e inflar el
            // saldo de la billetera de golpe sin que el usuario lo esperara.
            // Con el límite, se ponen al día solo los últimos 500 movimientos
            // pendientes y el resto queda pendiente para el comando programado
            // (ProcessRecurringIncome) que corre periódicamente.
            $maxBackfill = 500;
            $iteraciones = 0;

            if ($fechaIteracion->lte($hoy)) {
                do {
                    Transaction::create([
                        'user_id' => Auth::id(),
                        'wallet_origen_id' => null,
                        'wallet_destino_id' => $walletId,
                        'category_id' => $category->id,
                        'income_id' => $income->id,
                        'titulo' => $income->titulo,
                        'icono' => $ruta,
                        'monto' => $income->monto,
                        'tipo' => 'ingreso',
                        'fecha_ejecucion' => $fechaIteracion->format('Y-m-d H:i:s')
                    ]);

                    if ($walletId) {
                        Wallet::where('id', $walletId)
                            ->where('user_id', Auth::id())
                            ->increment('monto_actual', $income->monto);
                    }

                    $iteraciones++;

                    if ($frecuencia === 'ninguno') {
                        break;
                    }

                    $fechaIteracion = $this->avanzarFecha($fechaIteracion, $frecuencia)->startOfDay();

                } while ($fechaIteracion->lte($hoy) && $iteraciones < $maxBackfill);
            }

            if ($frecuencia !== 'ninguno') {
                $income->fecha_inicio = $fechaIteracion->format('Y-m-d H:i:s'); 
                $income->save();
            }

            DB::commit();
            
            // Ya funciona todo bien, regresamos el redireccionamiento normal quitando el dd()
            return $this->successResponse($request, 'Ingreso creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($request, 'Error: ' . $e->getMessage());
        }
    }

    private function avanzarFecha($fecha, $frecuencia) {
        switch ($frecuencia) {
            case 'diario': return $fecha->addDay();
            case 'semanal': return $fecha->addWeek();
            case 'quincenal': return $fecha->addDays(15);
            case 'mensual': return $fecha->addMonth();
            case 'anual': return $fecha->addYear();
            default: return $fecha;
        }
    }
    public function investment_create(Request $request)
        {
            // Mensajes personalizados en español para validaciones
            $messages = [
                'required' => 'El campo :attribute es obligatorio.',
                'numeric'  => 'El campo :attribute debe ser un valor numérico.',
                'date'     => 'El campo :attribute debe ser una fecha válida.',
            ];

            // Nombres amigables para los atributos
            $attributes = [
                'investment-titulo'              => 'Nombre de Inversión',
                'investment-monto'               => 'Monto Inicial',
                'investment-wallet-select-value' => 'Billetera Origen',
                'investment-renta-select-value'  => 'Tipo de Renta',
                'investment-tasa'                => 'Tasa de Interés',
                'investment-vencimiento'         => 'Fecha de Vencimiento',
            ];

            $validator = Validator::make($request->all(), [
                'investment-titulo'              => 'required|max:25',
                'investment-category'            => 'nullable|max:50',
                'investment-monto'               => 'required|numeric|min:0.01',
                'investment-wallet-select-value' => 'required', 
                'investment-renta-select-value'  => 'required|in:fija,variable', 
                'investment-tasa'                => 'required_if:investment-renta-select-value,fija|nullable|numeric|min:0',
                'investment-vencimiento'         => 'required|date',
                'investment-image'               => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            ], $messages, $attributes);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    // Antes se usaba ->all() (lista plana de mensajes), lo que impedía
                    // que add_panel.js pudiera asociar cada error con su campo
                    // (data-error-for). ->errors() devuelve el MessageBag completo,
                    // que se serializa como { "campo": ["mensaje", ...] }.
                    'errors'  => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                $ruta = null;
                if ($request->hasFile('investment-image')) {
                    $ruta = $request->file('investment-image')->store('investments', 'public');
                }

                $monto = $request->input('investment-monto');
                $walletId = $request->input('investment-wallet-select-value');
                $tipoRenta = strtolower($request->input('investment-renta-select-value'));

                $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->findOrFail($walletId);

                if ($wallet->monto_actual < $monto) {
                    return response()->json([
                        'success' => false,
                        'errors'  => ['error' => 'Fondos insuficientes en la billetera origen elegida.']
                    ], 422);
                }

                $categoryId = null;
                if ($request->filled('investment-category')) {
                    $category = Category::firstOrCreate([
                        'user_id'   => Auth::id(),
                        'categoria' => trim($request->input('investment-category'))
                    ]);
                    $categoryId = $category->id;
                }

                Investment::create([
                    'user_id'           => Auth::id(),
                    'wallet_id'         => $wallet->id,
                    'titulo'            => $request->input('investment-titulo'),
                    'category_id'       => $categoryId,
                    'icono'             => $ruta, 
                    'monto_inicial'     => $monto,
                    'valor_actual'      => $monto,
                    'ganancia'          => 0.00,
                    'tipo_renta'        => $tipoRenta,
                    'tasa_interes'      => ($tipoRenta === 'fija') ? $request->input('investment-tasa') : null,
                    'fecha_vencimiento' => $request->input('investment-vencimiento'),
                    'estado'            => 'activa',
                ]);

                $wallet->decrement('monto_actual', $monto);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Inversión registrada correctamente.'
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors'  => ['Error al procesar la inversión: ' . $e->getMessage()]
                ], 500);
            }
        }
   public function create_goal(Request $request)
    {
        $request->validate([
            'goal-titulo'         => 'required|string|max:25',
            'goal-category'       => 'nullable|string|max:25',
            'goal-monto-objetivo' => 'required|numeric|min:0.01',
            'goal-monto-inicial'  => 'nullable|numeric|min:0',
            'goal-fecha-limite'   => 'required|date', 
            'goal-descripcion'    => 'nullable|string|max:500',
            'goal-image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', 
        ]);

        // Forzar que si es null, empiece en 0
        $montoInicial = $request->input('goal-monto-inicial') ?? 0;
        $montoObjetivo = $request->input('goal-monto-objetivo');

        if ((float)$montoInicial > (float)$montoObjetivo) {
            return $this->errorResponse($request, 'El monto inicial no puede ser mayor que el monto objetivo.');
        }

        DB::beginTransaction();

        try {
            // 2. Manejo dinámico de la categoría
            $categoryId = null;
            if ($request->filled('goal-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('goal-category'))
                ]);
                $categoryId = $category->id;
            }

            // 3. Manejo de la subida de la imagen
            $imagePath = null;
            if ($request->hasFile('goal-image') && $request->file('goal-image')->isValid()) {
                // Guarda el archivo en storage/app/public/goals con un nombre único aleatorio
                $imagePath = $request->file('goal-image')->store('goals', 'public');
            }

            // 4. Crear el registro mapeando tus campos fillable
            $goal = Goal::create([
                'user_id'        => Auth::id(),
                'category_id'    => $categoryId,
                'titulo'         => trim($request->input('goal-titulo')),
                // Guardamos la ruta de la imagen en 'icono'. Si no se subió nada, queda null.
                'icono'          => $imagePath, 
                'monto_inicial'  => $montoInicial,
                'monto_actual'   => $montoInicial, 
                'monto_objetivo' => $montoObjetivo,
                'descripcion'    => $request->input('goal-descripcion'),
                'fecha_limite'   => $request->input('goal-fecha-limite'),
                'estado'         => 'activa',
            ]);

            DB::commit();
            return $this->successResponse($request, '¡Meta creada exitosamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($request, 'Error al crear la meta: ' . $e->getMessage());
        }
    }
    public function create_payment_goal(Request $request)
    {
        $request->validate([
            'paygoal-titulo'              => 'required|string|max:25',
            'paygoal-category'            => 'nullable|string|max:50',
            'paygoal-monto'               => 'required|numeric|min:0.01',
            'paygoal-wallet-select-value' => 'nullable', 
            'paygoal-target-select-value' => 'required|exists:goals,id', 
            'paygoal-image'               => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,svg|max:2048',
        ]);
        
        DB::beginTransaction();
        $pathIcono = null; 

        try {
            $monto = $request->input('paygoal-monto');
            $goalId = $request->input('paygoal-target-select-value');
            
            $walletId = $request->input('paygoal-wallet-select-value');
            if ($walletId === 'Externa' || empty($walletId)) {
                $walletId = null;
            }

            $categoryId = null;
            if ($request->filled('paygoal-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('paygoal-category'))
                ]);
                $categoryId = $category->id;
            }

            // Subida forzada usando el objeto directo del request
            if ($request->hasFile('paygoal-image') && $request->file('paygoal-image')->isValid()) {
                $pathIcono = $request->file('paygoal-image')->store('comprobantes_metas', 'public');
            }

            $goal = Goal::where('user_id', Auth::id())->findOrFail($goalId);

            if ($walletId !== null) {
                $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->findOrFail($walletId);
                if ($wallet->monto_actual < $monto) {
                    DB::rollBack();
                    if ($pathIcono) { Storage::disk('public')->delete($pathIcono); }
                    return $this->errorResponse($request, 'Fondos insuficientes.', 422, true);
                }
                $wallet->decrement('monto_actual', $monto);
            }

            // Inserción explícita en la base de datos
            $nuevoAbono = new PaymentGoal();
            $nuevoAbono->goal_id = $goal->id;
            $nuevoAbono->wallet_id = $walletId;
            $nuevoAbono->category_id = $categoryId;
            $nuevoAbono->titulo = trim($request->input('paygoal-titulo'));
            $nuevoAbono->icono = $pathIcono;
            $nuevoAbono->monto = $monto;
            $nuevoAbono->save();

            $goal->increment('monto_actual', $monto);

            $goal->refresh(); 
            if ($goal->monto_actual >= $goal->monto_objetivo) {
                $goal->update(['estado' => 'completada']);
            }

            DB::commit();
            return $this->successResponse($request, '¡Aporte realizado correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return $this->errorResponse($request, 'Error: ' . $e->getMessage(), 422, true);
        }
    }
    public function create_debt(Request $request)
    {
        // 1. Validamos usando la clave exacta del formulario: 'debt-image'
        $request->validate([
            'debt-titulo'                 => 'required|string|max:25',
            'debt-category'               => 'nullable|string|max:50', 
            'debt-monto'                  => 'required|numeric|min:0.01',
            'debt-vencimiento'            => 'required|date',
            'debt-tasa'                   => 'nullable|numeric|min:0',
            'debt-prioridad-select-value' => 'required|string|in:Ninguno,media,alta,baja',
            'debt-image'                  => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048', // <-- CORREGIDO
        ]);

        try {
            $prioridad = $request->input('debt-prioridad-select-value');
            if ($prioridad === 'Ninguno' || empty($prioridad)) {
                $prioridad = 'media';
            }

            // Recuperamos el archivo usando la clave correcta
            $pathIcono = null;
            if ($request->hasFile('debt-image') && $request->file('debt-image')->isValid()) { // <-- CORREGIDO
                $pathIcono = $request->file('debt-image')->store('debts', 'public');
            }

            // 2. Manejo dinámico de categorías
            $categoryId = null;
            if ($request->filled('debt-category')) {
                $category = Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('debt-category'))
                ]);
                $categoryId = $category->id;
            }

            // 3. Creación del registro
            Debt::create([
                'user_id'           => Auth::id(),
                'category_id'       => $categoryId,
                'titulo'            => trim($request->input('debt-titulo')),
                'monto_inicial'     => $request->input('debt-monto'),
                'monto_actual'      => $request->input('debt-monto'), 
                'fecha_vencimiento' => $request->input('debt-vencimiento'),
                'tasa_interes'      => $request->input('debt-tasa', 0.00) ?? 0.00,
                'prioridad'         => $prioridad,
                'estado'            => 'pendiente',
                'icono'             => $pathIcono,
            ]);

            return $this->successResponse($request, '¡Deuda registrada exitosamente!');

        } catch (\Exception $e) {
            if (isset($pathIcono) && $pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return $this->errorResponse($request, 'Error al registrar: ' . $e->getMessage(), 422, true);
        }
    }
    public function create_paymentdebt(Request $request)
    {

        // 1. Modificamos la validación para que admita texto en billetera (por si viene "Externa") y categoría
        $request->validate([
            'payment-titulo'              => 'required|string|max:25',
            'payment-monto'               => 'required|numeric|min:0.01',
            'payment-target-select-value' => 'required|exists:debts,id', 
            'payment-wallet-select-value' => 'nullable|string', // Cambiado a string para aceptar "Externa"
            'payment-category'            => 'nullable|string|max:50', // Para la creación dinámica
            'paymentdebt-image'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction(); // Cambiado a control manual para mejor gestión de excepciones

        try {
            $debt = Debt::findOrFail($request->input('payment-target-select-value'));
            $montoPago = $request->input('payment-monto');

            // Manejo del select de billetera ("Externa" o vacío -> null)
            $walletId = $request->input('payment-wallet-select-value');
            if ($walletId === 'Externa' || empty($walletId)) {
                $walletId = null;
            }

            // Manejo dinámico de la categoría (Igual que en tus otros componentes)
            $categoryId = null;
            if ($request->filled('payment-category')) {
                $category = \App\Models\Category::firstOrCreate([
                    'user_id'   => Auth::id(),
                    'categoria' => trim($request->input('payment-category'))
                ]);
                $categoryId = $category->id;
            }

            // Procesar la imagen si se subió
            $pathIcono = null;
            if ($request->hasFile('paymentdebt-image')) {
                $pathIcono = $request->file('paymentdebt-image')->store('comprobantes_deudas', 'public');
            }

            // Si se seleccionó una billetera interna, verificar saldo y descontar
            if ($walletId !== null) {
                // CORREGIDO: faltaba el filtro por user_id — sin él, cualquier usuario
                // podía pasar el wallet_id de otra persona y descontarle saldo a una
                // billetera ajena (IDOR). También se agrega lockForUpdate.
                $wallet = Wallet::where('user_id', Auth::id())->lockForUpdate()->findOrFail($walletId);

                if ($wallet->monto_actual < $montoPago) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Fondos insuficientes en la billetera seleccionada.', 422, true);
                }

                $wallet->decrement('monto_actual', $montoPago); 
            }

            // Registrar la transacción con el ID dinámico de categoría
            PaymentDebt::create([
                'debt_id'     => $debt->id,
                'wallet_id'   => $walletId,
                'category_id' => $categoryId, // <-- Ahora guarda el ID de la categoría creada o encontrada
                'titulo'      => trim($request->input('payment-titulo')),
                'icono'       => $pathIcono,
                'monto'       => $montoPago,
                'pago_minimo' => $request->has('payment-minimo'), 
            ]);

            // Amortizar la deuda principal
            $debt->decrement('monto_actual', $montoPago);

            // Verificar si la deuda se liquidó por completo
            if ($debt->refresh()->monto_actual <= 0) {
                $debt->update([
                    'monto_actual' => 0,
                    'estado'       => 'pagada'
                ]);
            }

            DB::commit();
            return $this->successResponse($request, '¡Abono a la deuda registrado correctamente!');

        } catch (\Exception $e) {
            DB::rollBack();
            // Si falló la BD y se había subido un archivo, lo borramos
            if (isset($pathIcono) && $pathIcono) {
                Storage::disk('public')->delete($pathIcono);
            }
            return $this->errorResponse($request, 'Error al procesar el pago: ' . $e->getMessage(), 422, true);
        }
    }
   


}