<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitoring extends Model
{
    protected $fillable = [
        'monitoring_session_id',
        'movement_product_item_id',
        'movement_product_placement_item_id',
        'qr_data',
        'notes',
        'pest_type',
        'pest_quantity',
        'bait_status',
        'action_taken',
        'used_apparatus',
        'pest_trace',
        'inspection_note',
        'inspection_photos',
        'risk_level',
    ];

    protected function casts(): array
    {
        return [
            'inspection_photos' => 'array',
        ];
    }

    public function monitoringSession(): BelongsTo
    {
        return $this->belongsTo(MonitoringSession::class);
    }

    public function movementProductItem(): BelongsTo
    {
        return $this->belongsTo(MovementProductItem::class);
    }

    public function movementProductPlacementItem(): BelongsTo
    {
        return $this->belongsTo(MovementProductPlacementItem::class);
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
