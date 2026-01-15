<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'entity_id',
        'parent_id',
        'text',
        'path'
    ];

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

    protected static function booted()
    {
        static::creating(function (Comment $comment) {
            $comment->path = '';
        });

        static::created(function (Comment $comment) {
            if ($comment->parent_id) {
                $parent = Comment::find($comment->parent_id);

                $comment->updateQuietly([
                    'path' => $parent->path . '.' . str_pad($comment->id, 6, '0', STR_PAD_LEFT),
                ]);
            } else {
                $comment->updateQuietly([
                    'path' => str_pad($comment->id, 6, '0', STR_PAD_LEFT),
                ]);
            }
        });
    }
}
