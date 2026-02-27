<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movement extends Model
{
    protected $fillable = ['operation_type'];

    const OPERATION_COMPONENT_RECEIPT     = 'component_receipt';
    const OPERATION_PRODUCT_RECEIPT       = 'product_receipt';
    const OPERATION_COMPONENT_CONSUMPTION = 'component_consumption';

    public static function operationTypes(): array
    {
        return [
            self::OPERATION_COMPONENT_RECEIPT => 'კომპონენტის მიღება',
            self::OPERATION_PRODUCT_RECEIPT   => 'პროდუქტის მიღება',
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

    public function consumptionMovements(): HasMany
    {
        return $this->hasMany(Movement::class, 'source_movement_id');
    }
}
