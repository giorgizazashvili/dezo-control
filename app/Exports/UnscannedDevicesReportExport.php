<?php

namespace App\Exports;

use App\Models\MonitoringLog;
use App\Models\MovementProductPlacementItem;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnscannedDevicesReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?int $organizationId = null,
        private readonly bool $unscannedOnly = true,
    ) {}

    public function query()
    {
        $query = MovementProductPlacementItem::query()
            ->whereNotNull('movement_product_placement_items.unique_code')
            ->with(['movement.organization', 'productSettlement'])
            ->addSelect([
                'last_scan_at' => MonitoringLog::query()
                    ->selectRaw('MAX(created_at)')
                    ->whereColumn('unique_code', 'movement_product_placement_items.unique_code'),
                'last_scan_type' => MonitoringLog::query()
                    ->select('type')
                    ->whereColumn('unique_code', 'movement_product_placement_items.unique_code')
                    ->latest()
                    ->limit(1),
            ])
            ->orderBy('unique_code');

        if ($this->organizationId) {
            $query->whereHas('movement', fn ($q) => $q->where('organization_id', $this->organizationId));
        }

        if ($this->unscannedOnly) {
            $query->whereNotIn(
                'movement_product_placement_items.unique_code',
                MonitoringLog::select('unique_code')->whereNotNull('unique_code')
            );
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'მოწყობილობის ID',
            'ობიექტი',
            'ადგილმდებარეობა',
            'მოწყობილობა',
            'ბოლო სკანირება',
            'სკანირების სტატუსი',
        ];
    }

    /** @param MovementProductPlacementItem $row */
    public function map($row): array
    {
        $locationParts = array_filter([$row->zone, $row->location]);

        $scanStatus = match ($row->last_scan_type) {
            'inspection' => 'შემოწმება',
            'replacement' => 'ამოცვლა',
            default => 'დაუსკანირებელი',
        };

        return [
            $row->unique_code ?? '',
            $row->movement?->organization?->name ?? '',
            implode(' / ', $locationParts),
            $row->productSettlement?->name ?? '',
            $row->last_scan_at ? Carbon::parse($row->last_scan_at)->format('d.m.Y H:i') : '',
            $scanStatus,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ],
        ];
    }
}
