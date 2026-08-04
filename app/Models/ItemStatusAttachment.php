<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemStatusAttachment extends Model
{
    use HasUuids, HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'item_status_id',
        'name',
        'description',
        'path',
        'mime_type',
        'size',
    ];

    public function itemStatus(): BelongsTo
    {
        return $this->belongsTo(ItemStatus::class);
    }
}
