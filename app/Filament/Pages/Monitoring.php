<?php

namespace App\Filament\Pages;

use App\Models\Movement;
use App\Models\MovementComponentItem;
use App\Models\Organization;
use App\Models\ProductSettlement;
use App\Services\MovementService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Monitoring extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-eye';

    protected static ?string $navigationLabel = 'მონიტორინგი';

    protected static ?string $title = 'მონიტორინგი';

    protected static ?int $navigationSort = 11;

    protected string $view = 'filament.pages.monitoring';

    public ?int $organizationId = null;

    public ?array $scannedData = null;

    public ?array $components = null;

    // Write-off modal state
    public ?int   $writeOffComponentId = null;
    public float  $writeOffQuantity    = 0;
    public bool   $showWriteOffModal   = false;

    // Replacement modal state
    public ?int   $replacementComponentId = null;
    public float  $replacementQuantity    = 0;
    public bool   $showReplacementModal   = false;

    public function processQr(string $json): void
    {
        $data = json_decode($json, true);

        if (! $data || ! isset($data['product_id'])) {
            Notification::make()->title('არასწორი QR კოდი')->danger()->send();

            return;
        }

        $product = ProductSettlement::with(['dimension', 'items.settlementComponent.dimension'])
            ->find($data['product_id']);

        if (! $product) {
            Notification::make()->title('პროდუქტი ვერ მოიძებნა')->danger()->send();

            return;
        }

        $service = app(MovementService::class);

        $this->scannedData = $data;
        $this->components  = $this->buildComponentsArray($product, $service);
    }

    public function openWriteOff(int $componentId, float $needed): void
    {
        $this->writeOffComponentId = $componentId;
        $this->writeOffQuantity    = $needed;
        $this->showWriteOffModal   = true;
    }

    public function confirmWriteOff(): void
    {
        if (! $this->writeOffComponentId || $this->writeOffQuantity <= 0) {
            return;
        }

        $movement = Movement::create([
            'operation_type'  => Movement::OPERATION_COMPONENT_CONSUMPTION,
            'organization_id' => $this->organizationId,
            'comment'         => 'მონიტორინგი — ჩამოწერა',
        ]);

        MovementComponentItem::create([
            'movement_id'             => $movement->id,
            'settlement_component_id' => $this->writeOffComponentId,
            'quantity'                => $this->writeOffQuantity,
        ]);

        Notification::make()->title('კომპონენტი ჩამოიწერა')->success()->send();

        $this->showWriteOffModal = false;
        $this->refreshComponents();
    }

    public function openReplacement(int $componentId, float $needed): void
    {
        $this->replacementComponentId = $componentId;
        $this->replacementQuantity    = $needed;
        $this->showReplacementModal   = true;
    }

    public function confirmReplacement(): void
    {
        if (! $this->replacementComponentId || $this->replacementQuantity <= 0) {
            return;
        }

        $movement = Movement::create([
            'operation_type'  => Movement::OPERATION_COMPONENT_RECEIPT,
            'organization_id' => $this->organizationId,
            'comment'         => 'მონიტორინგი — შეცვლა',
        ]);

        MovementComponentItem::create([
            'movement_id'             => $movement->id,
            'settlement_component_id' => $this->replacementComponentId,
            'quantity'                => $this->replacementQuantity,
        ]);

        Notification::make()->title('კომპონენტი შეიცვალა')->success()->send();

        $this->showReplacementModal = false;
        $this->refreshComponents();
    }

    public function resetScan(): void
    {
        $this->scannedData  = null;
        $this->components   = null;
        $this->dispatch('reset-scanner');
    }

    public function getOrganizations(): \Illuminate\Database\Eloquent\Collection
    {
        return Organization::orderBy('name')->get();
    }

    public function getComponentName(int $componentId): string
    {
        return collect($this->components ?? [])
            ->firstWhere('id', $componentId)['name'] ?? '';
    }

    private function refreshComponents(): void
    {
        if (! $this->scannedData) {
            return;
        }

        $product = ProductSettlement::with(['items.settlementComponent.dimension'])
            ->find($this->scannedData['product_id']);

        if (! $product) {
            return;
        }

        $this->components = $this->buildComponentsArray($product, app(MovementService::class));
    }

    private function buildComponentsArray(ProductSettlement $product, MovementService $service): array
    {
        return $product->items->map(function ($item) use ($service) {
            $component = $item->settlementComponent;
            $stock     = $service->getComponentStock($component->id);

            return [
                'id'        => $component->id,
                'name'      => $component->name,
                'dimension' => $component->dimension?->name ?? '',
                'needed'    => (float) $item->quantity,
                'stock'     => round($stock, 4),
            ];
        })->toArray();
    }
}
