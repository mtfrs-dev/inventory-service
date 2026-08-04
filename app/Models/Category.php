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

class Category extends Model implements AuditableContract
{
    use HasUuids, Auditable, HasFactory, SoftDeletes;

    protected $keyType      = 'string';
    public $incrementing    = false;

    protected $fillable = [
        'project_id',
        'external_project_id',
        'external_id',
        'code',
        'name',
        'description',
        'expected_items_count',
        'received_items_count'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function subcategories(): HasMany
    {
        
        return $this->hasMany(Subcategory::class, 'category_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_id', 'id');
    }
}
