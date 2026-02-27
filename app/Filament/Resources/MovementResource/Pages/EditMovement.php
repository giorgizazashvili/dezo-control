<?php

namespace App\Filament\Resources\MovementResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MovementResource;
use App\Models\Movement;
use App\Services\MovementService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMovement extends EditRecord
{
    protected static string $resource = MovementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('წაშლა'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $movement = $this->record;

        if ($movement->operation_type !== Movement::OPERATION_PRODUCT_RECEIPT) {
            return;
        }

        $service = app(MovementService::class);

        // ძველი ჩამოჭრის გაუქმება
        $service->reverseProductReceipt($movement);

        try {
            $service->processProductReceipt($movement);
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
