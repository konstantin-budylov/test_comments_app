<?php

namespace App\Enums;

enum EntityTypes: int
{
    case NEWS = 1;
    case VIDEO_POST = 2;

    public function modelClass(): string
    {
        return match ($this) {
            self::NEWS => \App\Models\News::class,
            self::VIDEO_POST => \App\Models\VideoPost::class,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::NEWS => 'news',
            self::VIDEO_POST => 'video',
        };
    }
}
