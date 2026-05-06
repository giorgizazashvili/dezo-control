<?php

namespace App\Filament\Resources\UnscannedDevicesReportResource\Pages;

use App\Filament\Resources\UnscannedDevicesReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListUnscannedDevicesReports extends ListRecords
{
    protected static string $resource = UnscannedDevicesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel-ში ჩამოტვირთვა')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('export.unscanned-devices-report', [
                    'organization_id' => data_get($this->tableFilters, 'organization.value'),
                    'unscanned_only' => data_get($this->tableFilters, 'unscanned_only.isActive') ? '1' : '0',
                ]))
                ->openUrlInNewTab(),
        ];
    }
}
