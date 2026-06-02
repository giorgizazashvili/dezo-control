<?php

namespace App\Exports;

use App\Models\MonitoringComponentReplacement;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PesticideReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?int $organizationId = null,
        private readonly ?string $from = null,
        private readonly ?string $until = null,
    ) {}

    public function query()
    {
        return MonitoringComponentReplacement::query()
            ->with([
                'monitoring.monitoringSession.organization',
                'monitoring.movementProductItem.productSettlement',
                'settlementComponent.dimension',
            ])
            ->when($this->organizationId, fn ($q) => $q->whereHas(
                'monitoring.monitoringSession', fn ($mq) => $mq->where('organization_id', $this->organizationId)
            ))
            ->when($this->from, fn ($q) => $q->whereHas(
                'monitoring', fn ($mq) => $mq->whereDate('created_at', '>=', $this->from)
            ))
            ->when($this->until, fn ($q) => $q->whereHas(
                'monitoring', fn ($mq) => $mq->whereDate('created_at', '<=', $this->until)
            ))
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'სამიზნე მავნებელი',
            'პრეპარატი',
            'რაოდენობა',
            'დოზა',
            'მეთოდი',
            'მოწყობილობა',
            'ობიექტი',
            'თარიღი',
        ];
    }

    /** @param MonitoringComponentReplacement $row */
    public function map($row): array
    {
        $quantity = $row->quantity
            ? rtrim(rtrim(number_format((float) $row->quantity, 4, '.', ''), '0'), '.')
            : '';

        return [
            $row->monitoring?->pest_type ?? '',
            $row->settlementComponent?->name ?? '',
            $quantity,
            $row->settlementComponent?->dimension?->name ?? '',
            $row->monitoring?->action_taken ?? '',
            $row->monitoring?->movementProductItem?->productSettlement?->name ?? '',
            $row->monitoring?->monitoringSession?->organization?->name ?? '',
            $row->monitoring?->created_at ? Carbon::parse($row->monitoring->created_at)->format('d.m.Y H:i') : '',
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
