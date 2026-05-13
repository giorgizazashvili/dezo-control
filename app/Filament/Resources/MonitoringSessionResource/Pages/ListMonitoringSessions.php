<?php

namespace App\Filament\Resources\MonitoringSessionResource\Pages;

use App\Filament\Resources\MonitoringSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringSessions extends ListRecords
{
    protected static string $resource = MonitoringSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('მონიტორინგის დაწყება'),
        ];
    }
}
