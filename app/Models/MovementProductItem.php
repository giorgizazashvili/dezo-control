<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MovementProductItem extends Model
{
    protected $fillable = [
        'movement_id',
        'product_settlement_id',
        'quantity',
        'qr_code',
        'uuid',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function productSettlement(): BelongsTo
    {
        return $this->belongsTo(ProductSettlement::class);
    }
}
