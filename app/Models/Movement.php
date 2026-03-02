<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movement extends Model
{
    protected $fillable = ['operation_type', 'organization_id', 'comment', 'source_movement_id', 'source_monitoring_id'];

    const OPERATION_COMPONENT_RECEIPT     = 'component_receipt';
    const OPERATION_PRODUCT_RECEIPT       = 'product_receipt';
    const OPERATION_COMPONENT_CONSUMPTION = 'component_consumption';
    const OPERATION_PRODUCT_PLACEMENT     = 'product_placement';

    public static function operationTypes(): array
    {
        return [
            self::OPERATION_COMPONENT_RECEIPT => 'კომპონენტის მიღება',
            self::OPERATION_PRODUCT_RECEIPT   => 'პროდუქტის მიღება',
            self::OPERATION_PRODUCT_PLACEMENT => 'ობიექტზე განთავსება',
        ];
    }

    public function componentItems(): HasMany
    {
        return $this->hasMany(MovementComponentItem::class);
    }

    public function productItems(): HasMany
    {
        return $this->hasMany(MovementProductItem::class);
    }

    public function placementItems(): HasMany
    {
        return $this->hasMany(MovementProductPlacementItem::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function consumptionMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'source_movement_id');
    }
}
