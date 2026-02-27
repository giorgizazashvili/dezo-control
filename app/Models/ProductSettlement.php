<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductSettlement extends Model
{
    protected $fillable = ['name', 'dimension_id'];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductSettlementItem::class);
    }
}
