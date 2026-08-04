<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ItemStatus extends Pivot
{
    use HasUuids;

    protected $table = 'item_status';

    public $incrementing = false;
    public $timestamps   = true;
    protected $keyType   = 'string';

    protected $fillable = [
        'item_id',
        'status_id',
        'is_active',
        'previous_status_id',
        'updated_by',
        'reason',
        'location_name',
        'location_address',
        'effective_at',
        'ineffective_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'      => 'boolean',
            'effective_at'   => 'datetime',
            'ineffective_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function previousStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'previous_status_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ItemStatusAttachment::class);
    }
}
