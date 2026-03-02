<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringComponentReplacement extends Model
{
    protected $fillable = [
        'monitoring_id',
        'settlement_component_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function monitoring(): BelongsTo
    {
        return $this->belongsTo(Monitoring::class);
    }

    public function settlementComponent(): BelongsTo
    {
        return $this->belongsTo(SettlementComponent::class);
    }
}
