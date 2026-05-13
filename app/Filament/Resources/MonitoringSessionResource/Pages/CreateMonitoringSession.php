<?php

namespace App\Filament\Resources\MonitoringSessionResource\Pages;

use App\Filament\Resources\MonitoringSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMonitoringSession extends CreateRecord
{
    protected static string $resource = MonitoringSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return ViewMonitoringSession::getUrl(['record' => $this->record]);
    }
}
