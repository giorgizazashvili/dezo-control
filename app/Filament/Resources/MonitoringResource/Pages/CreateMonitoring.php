<?php

namespace App\Filament\Resources\MonitoringResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MonitoringResource;
use App\Filament\Resources\MonitoringSessionResource;
use App\Services\MonitoringService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMonitoring extends CreateRecord
{
    protected static string $resource = MonitoringResource::class;

    public function mount(): void
    {
        abort_unless((bool) request()->query('session_id'), 403);
        parent::mount();
    }

    protected function afterCreate(): void
    {
        try {
            app(MonitoringService::class)->processComponentReplacements($this->record);
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

    protected function getRedirectUrl(): string
    {
        return MonitoringSessionResource::getUrl('view', ['record' => $this->record->monitoring_session_id]);
    }
}
