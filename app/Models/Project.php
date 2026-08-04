<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Project extends Model implements AuditableContract
{
    use HasUuids, Auditable, HasFactory;
    
    protected $keyType      = 'string';
    public $incrementing    = false;

    protected $fillable = [
        'external_id',
        'name',
        'pic_id',
        'code',
        'year',
        'last_synced_at',
    ];

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'project_id', 'id');
    }
}
