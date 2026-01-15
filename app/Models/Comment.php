<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['user_id', 'entity_id', 'parent_id', 'text'];

    final public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    final public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    final public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id');
    }

    final public function children()
    {
        return $this->hasMany(__CLASS__, 'parent_id')
            ->orderBy('created_at', 'desc')
            ->with('children'); // рекурсия
    }
}
