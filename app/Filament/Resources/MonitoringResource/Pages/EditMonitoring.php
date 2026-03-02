<?php

namespace App\Filament\Resources\MonitoringResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MonitoringResource;
use App\Services\MonitoringService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMonitoring extends EditRecord
{
    protected static string $resource = MonitoringResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $monitoring = $this->record->load('movementProductItem.productSettlement.dimension', 'movementProductItem.movement');
        $item = $monitoring->movementProductItem;

        if ($item) {
            $product = $item->productSettlement;
            $data['_box_product']   = $product->name . ' — ' . ($product->dimension?->name ?? '');
            $data['_box_quantity']  = rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.');
            $data['_box_date']      = $item->movement->created_at->format('d.m.Y H:i');
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $monitoring = $this->record;
        $service    = app(MonitoringService::class);

        try {
            $service->reverseComponentReplacements($monitoring);
            $service->processComponentReplacements($monitoring);
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
