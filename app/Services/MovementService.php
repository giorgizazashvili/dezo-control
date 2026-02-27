<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Movement;
use App\Models\MovementComponentItem;
use App\Models\SettlementComponent;

class MovementService
{
    /**
     * პროდუქტის მიღების დამუშავება:
     * 1. კომპონენტების ნაშთის შემოწმება
     * 2. კომპონენტების ჩამოჭრა (consumption movement)
     */
    public function processProductReceipt(Movement $movement): void
    {
        $movement->load('productItems.productSettlement.items');

        $required = $this->calculateRequiredComponents($movement);

        $this->checkStock($required);

        $this->createConsumptionMovement($movement, $required);
    }

    /**
     * პროდუქტის მიღების შეცვლისას — ძველი ჩამოჭრის გაუქმება
     */
    public function reverseProductReceipt(Movement $movement): void
    {
        $movement->consumptionMovements()->each(function (Movement $consumption) {
            $consumption->componentItems()->delete();
            $consumption->delete();
        });
    }

    /**
     * კომპონენტის მიმდინარე ნაშთი
     */
    public function getComponentStock(int $componentId): float
    {
        $received = MovementComponentItem::query()
            ->whereHas('movement', fn ($q) => $q->where('operation_type', Movement::OPERATION_COMPONENT_RECEIPT))
            ->where('settlement_component_id', $componentId)
            ->sum('quantity');

        $consumed = MovementComponentItem::query()
            ->whereHas('movement', fn ($q) => $q->where('operation_type', Movement::OPERATION_COMPONENT_CONSUMPTION))
            ->where('settlement_component_id', $componentId)
            ->sum('quantity');

        return (float) $received - (float) $consumed;
    }

    // ─── private ──────────────────────────────────────────────────────────────

    /**
     * @return array<int, float>  componentId → required_quantity
     */
    private function calculateRequiredComponents(Movement $movement): array
    {
        $required = [];

        foreach ($movement->productItems as $productItem) {
            foreach ($productItem->productSettlement->items as $settlementItem) {
                $componentId = $settlementItem->settlement_component_id;
                $qty         = (float) $settlementItem->quantity * (float) $productItem->quantity;

                $required[$componentId] = ($required[$componentId] ?? 0.0) + $qty;
            }
        }

        return $required;
    }

    /**
     * @throws InsufficientStockException
     */
    private function checkStock(array $required): void
    {
        $shortages = [];

        foreach ($required as $componentId => $neededQty) {
            $available = $this->getComponentStock($componentId);

            if ($available < $neededQty) {
                $component  = SettlementComponent::with('dimension')->find($componentId);
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

    private function createConsumptionMovement(Movement $movement, array $required): void
    {
        $consumption = Movement::create([
            'operation_type'    => Movement::OPERATION_COMPONENT_CONSUMPTION,
            'source_movement_id' => $movement->id,
        ]);

        foreach ($required as $componentId => $qty) {
            MovementComponentItem::create([
                'movement_id'              => $consumption->id,
                'settlement_component_id'  => $componentId,
                'quantity'                 => $qty,
            ]);
        }
    }
}
