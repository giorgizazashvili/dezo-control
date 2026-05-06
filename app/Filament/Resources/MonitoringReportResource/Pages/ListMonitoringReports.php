<?php

namespace App\Filament\Resources\MonitoringReportResource\Pages;

use App\Filament\Resources\MonitoringReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringReports extends ListRecords
{
    protected static string $resource = MonitoringReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel-ში ჩამოტვირთვა')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('export.monitoring-report', array_filter([
                    'organization_id' => data_get($this->tableFilters, 'organization_id.value'),
                    'from' => data_get($this->tableFilters, 'created_at.from'),
                    'until' => data_get($this->tableFilters, 'created_at.until'),
                ])))
                ->openUrlInNewTab(),
        ];
    }
}
