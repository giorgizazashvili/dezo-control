<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringLog extends Model
{
    protected $fillable = [
        'monitoring_id',
        'organization_id',
        'movement_product_item_id',
        'type',
        'settlement_component_id',
        'replaced_settlement_component_id',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(Monitoring::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function movementProductItem(): BelongsTo
    {
        return $this->belongsTo(MovementProductItem::class);
    }

    public function settlementComponent(): BelongsTo
    {
        return $this->belongsTo(SettlementComponent::class);
    }

    public function replacedComponent(): BelongsTo
    {
        return $this->belongsTo(SettlementComponent::class, 'replaced_settlement_component_id');
    }
}
