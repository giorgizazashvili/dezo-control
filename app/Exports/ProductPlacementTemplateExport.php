<?php

namespace App\Exports;

use App\Models\ProductSettlement;
use App\Services\MovementService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductPlacementTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new ProductPlacementTemplateSheet,
            new ProductPlacementProductsReferenceSheet,
        ];
    }
}

class ProductPlacementTemplateSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    public function collection(): Collection
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'პროდუქტი (სახელი)',
            'რაოდენობა',
            'უნიკალური კოდი',
            'ზონა',
            'მდებარეობა',
        ];
    }

    public function title(): string
    {
        return 'შაბლონი';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E1F2'],
                ],
            ],
        ];
    }
}

class ProductPlacementProductsReferenceSheet implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function query()
    {
        return ProductSettlement::query()->with('dimension')->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'სახელი',
            'განზომილება',
            'ნაშთი',
        ];
    }

    /** @param ProductSettlement $row */
    public function map($row): array
    {
        $stock = app(MovementService::class)->getProductStock($row->id);

        return [
            $row->name,
            $row->dimension?->name ?? '',
            number_format($stock, 4, '.', ''),
        ];
    }

    public function title(): string
    {
        return 'პროდუქტები (ცნობარი)';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ],
        ];
    }
}
