<?php

namespace App\Filament\Resources\MonitoringResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MonitoringResource;
use App\Services\MonitoringService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMonitoring extends CreateRecord
{
    protected static string $resource = MonitoringResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $monitoring = $this->record;

        try {
            app(MonitoringService::class)->processComponentReplacements($monitoring);
        } catch (InsufficientStockException $e) {
            Notification::make()
                ->title('კომპონენტის ნაშთი არ არის საკმარისი')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt(shouldRollbackDatabaseTransaction: true);
        }
    }
}
