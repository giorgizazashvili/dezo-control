<?php

namespace App\Exports;

use App\Models\Monitoring;
use App\Models\MonitoringLog;
use App\Models\Movement;
use App\Models\MovementProductPlacementItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title as ChartTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PestActivityReportExport implements WithCharts, WithEvents, WithTitle
{
    private const SHEET_TITLE = 'PestActivity';

    private const PEST_COLUMNS = [
        'rodent' => 'Rodent Activity / მღრღნელის აქტივობა სათაგური',
        'cockroach' => 'Cockroach Activity / ტარაკანის აქტივობა',
        'fly' => 'Fly Activity / ბუზის აქტივობა',
        'ant' => 'Ant Activity / ჭიანჭველის აქტივობა',
        'stored_product' => 'Stored Product Pest / საწყობის მავნებელის აქტივობა',
    ];

    /**
     * @var array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    private array $months = [];

    /** @var array<string, array<string, float>> */
    private array $matrix = [];

    /** @var array<string, float> */
    private array $deviceActivity = [];

    /** @var array<string, string> */
    private array $zoneRisks = [];

    public function __construct(
        private readonly int $organizationId,
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {
        $this->buildMonths();
        $this->buildData();
    }

    private function buildMonths(): void
    {
        $current = Carbon::parse($this->dateFrom)->startOfMonth();
        $end = Carbon::parse($this->dateTo)->endOfMonth();

        if ($current->year < 2000 || $current->gt($end) || $current->diffInMonths($end) > 120) {
            return;
        }

        while ($current->lte($end)) {
            $this->months[] = [
                'label' => $current->format('M Y'),
                'start' => $current->copy()->startOfMonth(),
                'end' => $current->copy()->endOfMonth(),
            ];
            $current->addMonth();
        }
    }

    private function mapPestType(string $pestType): ?string
    {
        $lower = mb_strtolower($pestType);

        if (str_contains($lower, 'თაგვ') || str_contains($lower, 'rodent') || str_contains($lower, 'mouse') || str_contains($lower, 'rat')) {
            return 'rodent';
        }

        if (str_contains($lower, 'ტარაკ') || str_contains($lower, 'cockroach')) {
            return 'cockroach';
        }

        if (str_contains($lower, 'ბუზ') || str_contains($lower, 'fly')) {
            return 'fly';
        }

        if (str_contains($lower, 'ჭიანჭ') || str_contains($lower, 'ant')) {
            return 'ant';
        }

        if (str_contains($lower, 'საწყობ') || str_contains($lower, 'stored')) {
            return 'stored_product';
        }

        return null;
    }

    private function buildData(): void
    {
        $emptyRow = array_fill_keys(array_keys(self::PEST_COLUMNS), 0.0);

        foreach ($this->months as $month) {
            $this->matrix[$month['label']] = $emptyRow;
        }

        $monitorings = Monitoring::query()
            ->whereHas('monitoringSession', fn ($q) => $q
                ->where('organization_id', $this->organizationId)
                ->whereBetween('started_at', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay(),
                ])
            )
            ->with('monitoringSession')
            ->whereNotNull('pest_type')
            ->get();

        foreach ($monitorings as $monitoring) {
            if (! $monitoring->monitoringSession) {
                continue;
            }

            $column = $this->mapPestType($monitoring->pest_type);

            if (! $column) {
                continue;
            }

            $label = Carbon::parse($monitoring->monitoringSession->started_at)->format('M Y');

            if (isset($this->matrix[$label][$column])) {
                $this->matrix[$label][$column] += (float) $monitoring->pest_quantity;
            }
        }

        $totalDevices = MovementProductPlacementItem::query()
            ->whereHas('movement', fn ($q) => $q
                ->where('organization_id', $this->organizationId)
                ->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT)
            )
            ->whereNotNull('unique_code')
            ->count();

        $inspectedByMonth = Monitoring::query()
            ->join('monitoring_sessions', 'monitorings.monitoring_session_id', '=', 'monitoring_sessions.id')
            ->where('monitoring_sessions.organization_id', $this->organizationId)
            ->whereBetween('monitoring_sessions.started_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->whereNotNull('monitorings.movement_product_placement_item_id')
            ->selectRaw('YEAR(monitoring_sessions.started_at) as yr, MONTH(monitoring_sessions.started_at) as mo, COUNT(DISTINCT monitorings.movement_product_placement_item_id) as cnt')
            ->groupBy('yr', 'mo')
            ->get()
            ->keyBy(fn ($row) => Carbon::createFromDate($row->yr, $row->mo, 1)->format('M Y'));

        foreach ($this->months as $month) {
            $inspected = $inspectedByMonth->get($month['label'])?->cnt ?? 0;
            $this->deviceActivity[$month['label']] = $totalDevices > 0
                ? round($inspected / $totalDevices * 100, 1)
                : 0.0;
        }

        $this->zoneRisks = MonitoringLog::query()
            ->where('organization_id', $this->organizationId)
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->whereNotNull('zone')
            ->where('zone', '!=', '')
            ->whereNotNull('risk_level')
            ->get()
            ->groupBy('zone')
            ->map(function ($logs) {
                $order = ['მაღალი / High' => 3, 'საშუალო / Medium' => 2, 'დაბალი / Low' => 1];

                return $logs->sortByDesc(fn ($l) => $order[$l->risk_level] ?? 0)->first()->risk_level;
            })
            ->toArray();
    }

    public function title(): string
    {
        return self::SHEET_TITLE;
    }

    /** @return Chart[] */
    public function charts(): array
    {
        if (empty($this->months)) {
            return [];
        }

        $monthCount = count($this->months);
        $dataEndRow = 1 + $monthCount;

        // Pest type columns: B(2) through F(6)
        $pestColCount = count(self::PEST_COLUMNS);
        $dataSeriesLabels = [];
        $dataSeriesValues = [];

        $xAxisValues = [
            new DataSeriesValues(
                'String',
                "'".self::SHEET_TITLE."'!\$A\$2:\$A\${$dataEndRow}",
                null,
                $monthCount
            ),
        ];

        foreach (array_keys(self::PEST_COLUMNS) as $index => $key) {
            $col = Coordinate::stringFromColumnIndex($index + 2);
            $dataSeriesLabels[] = new DataSeriesValues(
                'String',
                "'".self::SHEET_TITLE."'!\${$col}\$1",
                null,
                1
            );
            $dataSeriesValues[] = new DataSeriesValues(
                'Number',
                "'".self::SHEET_TITLE."'!\${$col}\$2:\${$col}\${$dataEndRow}",
                null,
                $monthCount
            );
        }

        $series = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, $pestColCount - 1),
            $dataSeriesLabels,
            $xAxisValues,
            $dataSeriesValues,
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $chartTitle = new ChartTitle("ტრენდ ანალიზი | {$this->dateFrom} — {$this->dateTo}");

        $chart = new Chart('pest_chart', $chartTitle, $legend, $plotArea);

        $chartStartRow = $dataEndRow + 2;
        $chartEndRow = $chartStartRow + 20;
        $lastPestCol = Coordinate::stringFromColumnIndex($pestColCount + 1); // F

        $chart->setTopLeftPosition("A{$chartStartRow}");
        $chart->setBottomRightPosition("{$lastPestCol}{$chartEndRow}");

        return [$chart];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $this->writeSheet($event->sheet->getDelegate());
            },
        ];
    }

    private function writeSheet(Worksheet $sheet): void
    {
        // Column H = index 8 (skip G=7)
        $activityColIndex = 8;
        $activityCol = 'H';

        $yellowBold = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];

        // Row 1: headers
        $sheet->setCellValue('A1', 'Month / თვე');
        $sheet->getStyle('A1')->applyFromArray($yellowBold);

        foreach (array_values(self::PEST_COLUMNS) as $index => $label) {
            $col = Coordinate::stringFromColumnIndex($index + 2);
            $sheet->setCellValue("{$col}1", $label);
            $sheet->getStyle("{$col}1")->applyFromArray($yellowBold);
        }

        $sheet->setCellValue("{$activityCol}1", 'შემოფსებული მოწყობილობების აქტივობა %');
        $sheet->getStyle("{$activityCol}1")->applyFromArray($yellowBold);
        $sheet->getRowDimension(1)->setRowHeight(50);

        // Data rows
        foreach ($this->months as $i => $month) {
            $row = $i + 2;
            $label = $month['label'];

            $sheet->setCellValue("A{$row}", $label);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            foreach (array_keys(self::PEST_COLUMNS) as $index => $key) {
                $col = Coordinate::stringFromColumnIndex($index + 2);
                $sheet->setCellValue("{$col}{$row}", $this->matrix[$label][$key] ?? 0.0);
                $sheet->getStyle("{$col}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->setCellValue("{$activityCol}{$row}", $this->deviceActivity[$label] ?? 0.0);
            $sheet->getStyle("{$activityCol}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-size columns A-H
        foreach (range(1, $activityColIndex) as $i) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Zone / risk table (after chart)
        $dataEndRow = 1 + count($this->months);
        $zoneStartRow = $dataEndRow + 24;
        $this->writeZoneTable($sheet, $zoneStartRow);
    }

    private function writeZoneTable(Worksheet $sheet, int $startRow): void
    {
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'],
            ],
        ];

        $sheet->setCellValue("A{$startRow}", 'ზონა');
        $sheet->setCellValue("B{$startRow}", 'რისკის დონეები');
        $sheet->getStyle("A{$startRow}:B{$startRow}")->applyFromArray($headerStyle);

        $row = $startRow + 1;
        foreach ($this->zoneRisks as $zone => $riskLevel) {
            $sheet->setCellValue("A{$row}", $zone);
            $sheet->setCellValue("B{$row}", $riskLevel);

            $color = match ($riskLevel) {
                'მაღალი / High' => 'CC0000',
                'საშუალო / Medium' => 'E65C00',
                'დაბალი / Low' => '007700',
                default => '000000',
            };

            $sheet->getStyle("B{$row}")->getFont()->getColor()->setRGB($color);
            $row++;
        }
    }
}
