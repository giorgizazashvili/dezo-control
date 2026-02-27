<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementComponent extends Model
{
    protected $fillable = ['name', 'dimension_id'];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(Dimension::class);
    }
}
