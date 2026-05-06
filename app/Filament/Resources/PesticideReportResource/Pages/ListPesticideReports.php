<?php

namespace App\Filament\Resources\PesticideReportResource\Pages;

use App\Filament\Resources\PesticideReportResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPesticideReports extends ListRecords
{
    protected static string $resource = PesticideReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Excel-ში ჩამოტვირთვა')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('export.pesticide-report', array_filter([
                    'organization_id' => data_get($this->tableFilters, 'organization.value'),
                    'from' => data_get($this->tableFilters, 'created_at.from'),
                    'until' => data_get($this->tableFilters, 'created_at.until'),
                ])))
                ->openUrlInNewTab(),
        ];
    }
}
