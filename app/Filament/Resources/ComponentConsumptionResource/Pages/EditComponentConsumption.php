<?php

namespace App\Filament\Resources\ComponentConsumptionResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\ComponentConsumptionResource;
use App\Services\MovementService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditComponentConsumption extends EditRecord
{
    protected static string $resource = ComponentConsumptionResource::class;

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
