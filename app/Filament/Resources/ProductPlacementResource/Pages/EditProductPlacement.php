<?php

namespace App\Filament\Resources\ProductPlacementResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ProductPlacementResource;
use App\Services\MovementService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProductPlacement extends EditRecord
{
    protected static string $resource = ProductPlacementResource::class;

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

        try {
            app(MovementService::class)->processProductPlacement($movement);
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
