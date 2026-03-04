<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Monitoring;
use App\Models\MonitoringComponentReplacement;
use App\Models\MonitoringLog;
use App\Models\Movement;
use App\Models\MovementComponentItem;
use App\Models\MovementProductItem;
use App\Models\MovementProductPlacementItem;
use App\Models\ProductSettlementItem;
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
     * ბოქსის მიმდინარე კომპონენტები სია.
     * ყველა ორიგინალი კომპონენტი, ბოლო ამოცვლაში გამოჩენილი ახალი ტიპები კი
     * ჩაანაცვლებს შესაბამის ორიგინალს (ვინც replacement-ში არ მოხვდა).
     */
    public function getProductComponentsWithStock(int $productSettlementId, ?int $movementProductItemId = null): array
    {
        $originalItems = ProductSettlementItem::where('product_settlement_id', $productSettlementId)
            ->get();

        $originalIds = $originalItems->pluck('settlement_component_id')->toArray();

        // ბოლო ამოცვლის კომპონენტები (თუ არსებობს)
        $lastReplacements = collect();
        if ($movementProductItemId) {
            $lastMonitoringId = MonitoringComponentReplacement::query()
                ->whereHas('monitoring', fn ($q) => $q->where('movement_product_item_id', $movementProductItemId))
                ->max('monitoring_id');

            if ($lastMonitoringId) {
                $lastReplacements = MonitoringComponentReplacement::where('monitoring_id', $lastMonitoringId)->get();
            }
        }

        if ($lastReplacements->isEmpty()) {
            return $originalItems->map(function (ProductSettlementItem $item) {
                return [
                    'settlement_component_id' => $item->settlement_component_id,
                    'quantity'                => null,
                    '_stock'                  => rtrim(rtrim(number_format((float) $item->quantity, 4, '.', ''), '0'), '.') ?: '0',
                ];
            })->all();
        }

        $replacedIds = $lastReplacements->pluck('settlement_component_id')->toArray();

        // ახალი ტიპები — replacement-ში არიან, ორიგინალში არ
        $newTypes = $lastReplacements->filter(fn ($r) => ! in_array($r->settlement_component_id, $originalIds))->values();
        $newTypeIndex = 0;

        $result = [];

        foreach ($originalItems as $original) {
            $origId = $original->settlement_component_id;

            if (in_array($origId, $replacedIds)) {
                // ამ ტიპის კომპონენტი ჩაანაცვლეს — ისევ იგივე ტიპია
                $result[] = [
                    'settlement_component_id' => $origId,
                    'quantity'                => null,
                    '_stock'                  => rtrim(rtrim(number_format((float) $original->quantity, 4, '.', ''), '0'), '.') ?: '0',
                ];
            } elseif ($newTypeIndex < $newTypes->count()) {
                // ეს slot-ი ახალი ტიპის კომპონენტმა დაიკავა — ნორმა ორიგინალიდან
                $newId = $newTypes[$newTypeIndex++]->settlement_component_id;
                $result[] = [
                    'settlement_component_id' => $newId,
                    'quantity'                => null,
                    '_stock'                  => rtrim(rtrim(number_format((float) $original->quantity, 4, '.', ''), '0'), '.') ?: '0',
                ];
            } else {
                // ამ ორიგინალს არ შეხებიათ — ნორმა
                $result[] = [
                    'settlement_component_id' => $origId,
                    'quantity'                => null,
                    '_stock'                  => rtrim(rtrim(number_format((float) $original->quantity, 4, '.', ''), '0'), '.') ?: '0',
                ];
            }
        }

        // ახალი ტიპები, რომლებიც ყველა ორიგინალ slot-ზე მეტია (დამატებული)
        while ($newTypeIndex < $newTypes->count()) {
            $newId = $newTypes[$newTypeIndex++]->settlement_component_id;
            $result[] = [
                'settlement_component_id' => $newId,
                'quantity'                => null,
                '_stock'                  => '0',
            ];
        }

        return $result;
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
        $monitoring->componentReplacements()
            ->where(fn ($q) => $q->whereNull('quantity')->orWhere('quantity', '<=', 0))
            ->delete();

        $monitoring->load('componentReplacements');

        $required = [];

        foreach ($monitoring->componentReplacements as $replacement) {
            $id = $replacement->settlement_component_id;
            $required[$id] = ($required[$id] ?? 0.0) + (float) $replacement->quantity;
        }

        if (empty($required)) {
            $this->writeLog($monitoring, []);
            return;
        }

        $this->checkComponentStock($required);

        $this->createMonitoringConsumption($monitoring, $required);
        $this->writeLog($monitoring, $required);
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

        MonitoringLog::where('monitoring_id', $monitoring->id)->delete();
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

    private function writeLog(Monitoring $monitoring, array $required): void
    {
        $base = [
            'monitoring_id'            => $monitoring->id,
            'organization_id'          => $monitoring->organization_id,
            'movement_product_item_id' => $monitoring->movement_product_item_id,
            'notes'                    => $monitoring->notes,
        ];

        if (empty($required)) {
            MonitoringLog::create(array_merge($base, ['type' => 'inspection']));
            return;
        }

        $monitoring->load('movementProductItem');
        $movementProductItemId = $monitoring->movement_product_item_id;
        $productSettlementId   = $monitoring->movementProductItem->product_settlement_id;

        // ბოქსის წინა მდგომარეობა (ამ მონიტორინგამდე რა კომპონენტები იყო)
        $previousMonitoringId = MonitoringComponentReplacement::query()
            ->whereHas('monitoring', fn ($q) => $q
                ->where('movement_product_item_id', $movementProductItemId)
                ->where('id', '!=', $monitoring->id))
            ->max('monitoring_id');

        if ($previousMonitoringId) {
            $previousIds = MonitoringComponentReplacement::where('monitoring_id', $previousMonitoringId)
                ->pluck('settlement_component_id')
                ->toArray();
        } else {
            $previousIds = ProductSettlementItem::where('product_settlement_id', $productSettlementId)
                ->pluck('settlement_component_id')
                ->toArray();
        }

        // ახალი ტიპები (required-ში არიან, წინა მდგომარეობაში — არა)
        $newTypeIds           = array_values(array_diff(array_keys($required), $previousIds));
        // წინა კომპონენტები, რომლებიც required-ში არ მოხვდნენ (ამათი slot-ები ახლებმა დაიკავეს)
        $uncoveredPreviousIds = array_values(array_diff($previousIds, array_keys($required)));

        // mapping: new_component_id => replaced_previous_id
        $substitutionMap = [];
        foreach ($newTypeIds as $i => $newId) {
            $substitutionMap[$newId] = $uncoveredPreviousIds[$i] ?? null;
        }

        foreach ($required as $componentId => $qty) {
            MonitoringLog::create(array_merge($base, [
                'type'                             => 'replacement',
                'settlement_component_id'          => $componentId,
                // თუ სხვა ტიპით ჩანაცვლება — substitutionMap, თუ იგივე ტიპი — $componentId (a→a)
                'replaced_settlement_component_id' => $substitutionMap[$componentId] ?? $componentId,
                'quantity'                         => $qty,
            ]));
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
