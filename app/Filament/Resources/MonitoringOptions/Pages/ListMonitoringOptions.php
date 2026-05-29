<?php

namespace App\Filament\Resources\MonitoringOptions\Pages;

use App\Filament\Resources\MonitoringOptions\MonitoringOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringOptions extends ListRecords
{
    protected static string $resource = MonitoringOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
