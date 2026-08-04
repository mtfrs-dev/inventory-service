<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Status extends Model implements AuditableContract
{
    use HasUuids, Auditable, HasFactory;

    protected $fillable = [
        'code',
        'label',
        'description'
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_status')
            ->using(ItemStatus::class)
            ->withPivot([
                'is_active',
                'previous_status_id',
                'updated_by',
                'reason',
                'location_name',
                'location_address',
                'effective_at',
                'ineffective_at',
            ])
            ->withTimestamps();
    }
}
