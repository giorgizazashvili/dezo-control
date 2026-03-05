<?php

namespace App\Filament\Resources\ProductReceiptResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ProductReceiptResource;
use App\Models\Movement;
use App\Services\MovementService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProductReceipt extends CreateRecord
{
    protected static string $resource = ProductReceiptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operation_type'] = Movement::OPERATION_PRODUCT_RECEIPT;
        return $data;
    }

    protected function afterCreate(): void
    {
        $movement = $this->record;

        try {
            app(MovementService::class)->processProductReceipt($movement);
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
