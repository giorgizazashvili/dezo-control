<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitoring extends Model
{
    protected $fillable = [
        'organization_id',
        'technician',
        'started_at',
        'finished_at',
        'movement_product_item_id',
        'qr_data',
        'notes',
        'pest_type',
        'pest_quantity',
        'bait_status',
        'action_taken',
        'inspection_note',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function movementProductItem(): BelongsTo
    {
        return $this->belongsTo(MovementProductItem::class);
    }

    public function componentReplacements(): HasMany
    {
        return $this->hasMany(MonitoringComponentReplacement::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class);
    }

    public function getParsedQrAttribute(): ?array
    {
        if (! $this->qr_data) {
            return null;
        }

        $decoded = json_decode($this->qr_data, true);

        return is_array($decoded) ? $decoded : null;
    }
}
