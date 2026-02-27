<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovementComponentItem extends Model
{
    protected $fillable = [
        'movement_id',
        'settlement_component_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function settlementComponent(): BelongsTo
    {
        return $this->belongsTo(SettlementComponent::class);
    }
}
