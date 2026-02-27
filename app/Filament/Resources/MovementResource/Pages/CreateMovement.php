<?php

namespace App\Filament\Resources\MovementResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MovementResource;
use App\Models\Movement;
use App\Services\MovementService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMovement extends CreateRecord
{
    protected static string $resource = MovementResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $movement = $this->record;
        $service  = app(MovementService::class);

        try {
            match ($movement->operation_type) {
                Movement::OPERATION_PRODUCT_RECEIPT   => $service->processProductReceipt($movement),
                Movement::OPERATION_PRODUCT_PLACEMENT => $service->processProductPlacement($movement),
                default                               => null,
            };
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
