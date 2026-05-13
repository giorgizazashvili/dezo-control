<?php

namespace App\Filament\Resources\MonitoringResource\Pages;

use App\Exceptions\InsufficientStockException;
use App\Filament\Resources\MonitoringResource;
use App\Filament\Resources\MonitoringSessionResource;
use App\Models\Movement;
use App\Models\MovementProductPlacementItem;
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
            Actions\DeleteAction::make()
                ->label('წაშლა')
                ->after(fn () => redirect(
                    MonitoringSessionResource::getUrl('view', ['record' => $this->record->monitoring_session_id])
                )),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $monitoring = $this->record->load(
            'movementProductItem.productSettlement.dimension',
            'monitoringSession'
        );

        $item = $monitoring->movementProductItem;

        if ($item) {
            $product = $item->productSettlement;
            $data['_device_name'] = $product->name.' — '.($product->dimension?->name ?? '');

            $placementItem = MovementProductPlacementItem::query()
                ->where('product_settlement_id', $item->product_settlement_id)
                ->whereHas('movement', fn ($q) => $q
                    ->where('organization_id', $monitoring->monitoringSession->organization_id)
                    ->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT)
                )
                ->latest('id')
                ->first();

            if ($placementItem) {
                $data['_device_location'] = implode(' / ', array_filter([
                    $placementItem->unique_code,
                    $placementItem->zone,
                    $placementItem->location,
                ]));
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $service = app(MonitoringService::class);

        try {
            $service->reverseComponentReplacements($this->record);
            $service->processComponentReplacements($this->record);
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
