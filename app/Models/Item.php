<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Item extends Model implements AuditableContract
{
    use HasUuids, Auditable, HasFactory, SoftDeletes;

    protected $keyType      = 'string';
    public $incrementing    = false;

    protected $fillable = [
        'project_id',
        'category_id',
        'subcategory_id',
        'serial_number',
        'code',
        'name',
        'description',
        'current_status_id',
        'qr_code',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id', 'id');
    }

    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'current_status_id', 'id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ItemAttachment::class);
    }

    public function statuses(): BelongsToMany
    {
        return $this->belongsToMany(Status::class, 'item_status')
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
