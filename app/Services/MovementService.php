<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Movement;
use App\Models\MovementComponentItem;
use App\Models\MovementProductItem;
use App\Models\MovementProductPlacementItem;
use App\Models\ProductSettlement;
use App\Models\SettlementComponent;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

class MovementService
{
    // ═══════════════════════════════════════════════════════════════
    // პროდუქტის მიღება
    // ═══════════════════════════════════════════════════════════════

    public function processProductReceipt(Movement $movement): void
    {
        $movement->load('productItems.productSettlement.items');

        $required = $this->calculateRequiredComponents($movement);

        $this->checkComponentStock($required);

        $this->createConsumptionMovement($movement, $required);

        $this->generateQrCodes($movement);
    }

    public function reverseProductReceipt(Movement $movement): void
    {
        $movement->consumptionMovements()->each(function (Movement $consumption) {
            $consumption->componentItems()->delete();
            $consumption->delete();
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // ობიექტზე განთავსება
    // ═══════════════════════════════════════════════════════════════

    public function processProductPlacement(Movement $movement): void
    {
        $movement->load('placementItems.productSettlement.dimension');

        $this->checkProductStock($movement->placementItems, $movement->id);
    }

    public function reverseProductPlacement(Movement $movement): void
    {
        // placement items are deleted via cascadeOnDelete when movement is deleted
        // for edit: Filament repeater handles deletion of old items automatically
    }

    // ═══════════════════════════════════════════════════════════════
    // ნაშთები
    // ═══════════════════════════════════════════════════════════════

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

    public function getProductStock(int $productSettlementId, ?int $excludeMovementId = null): float
    {
        $received = MovementProductItem::query()
            ->whereHas('movement', fn ($q) => $q->where('operation_type', Movement::OPERATION_PRODUCT_RECEIPT))
            ->where('product_settlement_id', $productSettlementId)
            ->sum('quantity');

        $placed = MovementProductPlacementItem::query()
            ->whereHas('movement', fn ($q) => $q->where('operation_type', Movement::OPERATION_PRODUCT_PLACEMENT))
            ->where('product_settlement_id', $productSettlementId)
            ->when($excludeMovementId, fn ($q) => $q->where('movement_id', '!=', $excludeMovementId))
            ->sum('quantity');

        return (float) $received - (float) $placed;
    }

    // ═══════════════════════════════════════════════════════════════
    // QR კოდი
    // ═══════════════════════════════════════════════════════════════

    public function generateQrSvg(string $data): string
    {
        $options = new QROptions([
            'outputType'     => QROutputInterface::MARKUP_SVG,
            'outputBase64'   => false,
            'eccLevel'       => QRCode::ECC_M,
            'svgViewBoxSize' => 400,
        ]);

        return (new QRCode($options))->render($data);
    }

    // ═══════════════════════════════════════════════════════════════
    // private
    // ═══════════════════════════════════════════════════════════════

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

    /** @throws InsufficientStockException */
    private function checkComponentStock(array $required): void
    {
        $shortages = [];

        foreach ($required as $componentId => $neededQty) {
            $available = $this->getComponentStock($componentId);

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

    /** @throws InsufficientStockException */
    private function checkProductStock(iterable $placementItems, ?int $excludeMovementId = null): void
    {
        $shortages = [];

        foreach ($placementItems as $item) {
            $productId = $item->product_settlement_id;
            $needed    = (float) $item->quantity;
            $available = $this->getProductStock($productId, $excludeMovementId);

            if ($available < $needed) {
                $product     = ProductSettlement::with('dimension')->find($productId);
                $shortages[] = [
                    'component' => $product->name,
                    'dimension' => $product->dimension?->name ?? '',
                    'needed'    => round($needed, 4),
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
            'operation_type'     => Movement::OPERATION_COMPONENT_CONSUMPTION,
            'source_movement_id' => $movement->id,
        ]);

        foreach ($required as $componentId => $qty) {
            MovementComponentItem::create([
                'movement_id'             => $consumption->id,
                'settlement_component_id' => $componentId,
                'quantity'                => $qty,
            ]);
        }
    }

    private function generateQrCodes(Movement $movement): void
    {
        $movement->load('productItems');

        foreach ($movement->productItems as $item) {
            $item->update(['qr_code' => $this->generateQrSvg($item->uuid)]);
        }
    }
}
