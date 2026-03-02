<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Monitoring;
use App\Models\MonitoringComponentReplacement;
use App\Models\Movement;
use App\Models\MovementComponentItem;
use App\Models\MovementProductItem;
use App\Models\MovementProductPlacementItem;
use App\Models\SettlementComponent;

class MonitoringService
{
    public function __construct(private readonly MovementService $movementService) {}

    // ═══════════════════════════════════════════════════════════════
    // QR კოდის დამუშავება
    // ═══════════════════════════════════════════════════════════════

    /**
     * სკანირებული UUID-იდან MovementProductItem-ის პოვნა.
     */
    public function findBoxFromQr(string $uuid): ?MovementProductItem
    {
        return MovementProductItem::query()
            ->where('uuid', trim($uuid))
            ->with(['productSettlement.dimension', 'movement'])
            ->first();
    }

    /**
     * პროდუქტის ბოლო განთავსების ობიექტის ID.
     */
    public function getPlacementOrganizationId(int $productSettlementId): ?int
    {
        $placement = MovementProductPlacementItem::query()
            ->where('product_settlement_id', $productSettlementId)
            ->whereHas('movement', fn ($q) => $q->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT))
            ->with('movement')
            ->latest('id')
            ->first();

        return $placement?->movement?->organization_id;
    }

    // ═══════════════════════════════════════════════════════════════
    // კომპონენტის ჩანაცვლება
    // ═══════════════════════════════════════════════════════════════

    /** @throws InsufficientStockException */
    public function processComponentReplacements(Monitoring $monitoring): void
    {
        $monitoring->load('componentReplacements');

        $required = [];

        foreach ($monitoring->componentReplacements as $replacement) {
            $id = $replacement->settlement_component_id;
            $required[$id] = ($required[$id] ?? 0.0) + (float) $replacement->quantity;
        }

        if (empty($required)) {
            return;
        }

        $this->checkComponentStock($required);

        $this->createMonitoringConsumption($monitoring, $required);
    }

    public function reverseComponentReplacements(Monitoring $monitoring): void
    {
        Movement::query()
            ->where('operation_type', Movement::OPERATION_COMPONENT_CONSUMPTION)
            ->where('source_monitoring_id', $monitoring->id)
            ->each(function (Movement $consumption) {
                $consumption->componentItems()->delete();
                $consumption->delete();
            });
    }

    // ═══════════════════════════════════════════════════════════════
    // private
    // ═══════════════════════════════════════════════════════════════

    /** @throws InsufficientStockException */
    private function checkComponentStock(array $required): void
    {
        $shortages = [];

        foreach ($required as $componentId => $neededQty) {
            $available = $this->movementService->getComponentStock($componentId);

            if ($available < $neededQty) {
                $component   = SettlementComponent::with('dimension')->find($componentId);
                $shortages[] = [
                    'component' => $component->name,
                    'dimension' => $component->dimension?->name ?? '',
                    'needed'    => round($neededQty, 4),
                    'available' => round($available, 4),
                ];
            }
        }

        if (! empty($shortages)) {
            throw new InsufficientStockException($shortages);
        }
    }

    private function createMonitoringConsumption(Monitoring $monitoring, array $required): void
    {
        $consumption = Movement::create([
            'operation_type'      => Movement::OPERATION_COMPONENT_CONSUMPTION,
            'source_monitoring_id' => $monitoring->id,
        ]);

        foreach ($required as $componentId => $qty) {
            MovementComponentItem::create([
                'movement_id'             => $consumption->id,
                'settlement_component_id' => $componentId,
                'quantity'                => $qty,
            ]);
        }
    }
}
