<?php

namespace App\Filament\Resources\MonitoringOptions\Pages;

use App\Filament\Resources\MonitoringOptions\MonitoringOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMonitoringOption extends EditRecord
{
    protected static string $resource = MonitoringOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
