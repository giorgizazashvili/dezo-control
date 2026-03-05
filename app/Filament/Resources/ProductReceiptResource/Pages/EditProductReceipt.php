<?php

namespace App\Filament\Resources\ProductReceiptResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ProductReceiptResource;
use App\Services\MovementService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProductReceipt extends EditRecord
{
    protected static string $resource = ProductReceiptResource::class;

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
        $service  = app(MovementService::class);

        try {
            $service->reverseProductReceipt($movement);
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
