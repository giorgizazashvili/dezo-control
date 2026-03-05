<?php

namespace App\Filament\Resources\ComponentConsumptionResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ComponentConsumptionResource;
use App\Models\Movement;
use App\Services\MovementService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateComponentConsumption extends CreateRecord
{
    protected static string $resource = ComponentConsumptionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operation_type'] = Movement::OPERATION_COMPONENT_CONSUMPTION;
        return $data;
    }

    protected function afterCreate(): void
    {
        $movement = $this->record;

        try {
            app(MovementService::class)->processComponentConsumption($movement);
        } catch (InsufficientStockException $e) {
            Notification::make()
                ->title('ნაშთი არ არის საკმარისი')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            $this->halt(shouldRollbackDatabaseTransaction: true);
        }
    }
}
