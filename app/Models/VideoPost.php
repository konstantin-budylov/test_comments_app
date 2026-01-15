<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoPost extends Model
{
    protected $fillable = ['title', 'description'];

    public function entity()
    {
        return $this->morphOne(Entity::class, 'entityable');
    }
}
