<?php

namespace App\Models;

use App\Enums\EntityTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Entity extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'entityable_id',
        'entityable_type',
    ];

    protected $casts = [
        'entityable_type' => EntityTypes::class,
    ];

    final public function entityable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType(Builder $query, EntityTypes $type): Builder
    {
        return $query->where('entityable_type', $type);
    }
}
