<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSettlementItem extends Model
{
    protected $fillable = [
        'product_settlement_id',
        'settlement_component_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function productSettlement(): BelongsTo
    {
        return $this->belongsTo(ProductSettlement::class);
    }

    public function settlementComponent(): BelongsTo
    {
        return $this->belongsTo(SettlementComponent::class);
    }
}
