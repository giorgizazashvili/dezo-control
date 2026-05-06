<?php

namespace App\Exports;

use App\Models\MonitoringLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MonitoringReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return MonitoringLog::query()
            ->with(['movementProductItem.productSettlement', 'organization'])
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'მოწყობილობის ID',
            'ადგილმდებარეობა',
            'მოწყობილობა',
            'სატყუარის მდგომარეობა',
            'სკანირების დრო',
            'სკანირების სტატუსი',
            'მოწყობილობის მდგომარეობა',
            'რაოდენობა',
            'რისკის დონე',
        ];
    }

    /** @param MonitoringLog $row */
    public function map($row): array
    {
        $locationParts = array_filter([$row->zone, $row->location]);

        $scanStatus = match ($row->type) {
            'inspection' => 'შემოწმება',
            'replacement' => 'ამოცვლა',
            default => $row->type,
        };

        $quantity = $row->pest_quantity
            ? rtrim(rtrim(number_format((float) $row->pest_quantity, 4, '.', ''), '0'), '.')
            : '';

        return [
            $row->unique_code ?? '',
            implode(' / ', $locationParts),
            $row->movementProductItem?->productSettlement?->name ?? '',
            $row->bait_status ?? '',
            $row->created_at?->format('d.m.Y H:i') ?? '',
            $scanStatus,
            $row->inspection_status ?? '',
            $quantity,
            $row->risk_level ?? '',
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
