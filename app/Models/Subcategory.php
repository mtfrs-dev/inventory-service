<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Subcategory extends Model implements AuditableContract
{
    use HasUuids, Auditable, HasFactory, SoftDeletes;

    protected $keyType      = 'string';
    public $incrementing    = false;

    protected $fillable = [
        'category_id',
        'external_id',
        'external_category_id',
        'code',
        'name',
        'description',
        'expected_items_count',
        'received_items_count'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'subcategory_id', 'id');
    }
}
