<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'text'       => $this->text,
            'parent_id'  => $this->parent_id,
            'created_at' => $this->created_at,
            'children'   => self::collection(
                $this->children ?? []
            ),
        ];
    }
}
