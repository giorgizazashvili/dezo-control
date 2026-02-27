<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovementProductPlacementItem extends Model
{
    protected $fillable = [
        'movement_id',
        'product_settlement_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function productSettlement(): BelongsTo
    {
        return $this->belongsTo(ProductSettlement::class);
    }
}
