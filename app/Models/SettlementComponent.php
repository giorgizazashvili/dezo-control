<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettlementComponent extends Model
{
    protected $fillable = ['name', 'dimension_id'];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }

    public function movementProductItems(): HasMany
    {
        return $this->hasMany(MovementProductItem::class);
    }

    public function productSettlementItems(): HasMany
    {
        return $this->hasMany(ProductSettlementItem::class);
    }

    public function monitoringComponentReplacements(): HasMany
    {
        return $this->hasMany(MonitoringComponentReplacement::class);
    }
}
