<?php

namespace App\Http\Resources;

use App\Enums\EntityTypes;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property $id
 * @property $entityable_type
 * @property mixed $entityable
 * @property $created_at
 */
class EntityResource extends JsonResource
{
    final public function toArray($request): array
    {
        $type = $this->entityable_type instanceof EntityTypes
            ? $this->entityable_type
            : EntityTypes::tryFrom($this->entityable_type);

        return [
            'entity_id'         => $this->id,
            'entity_type'      => $type?->label(),
            'entity_data'       => $this->whenLoaded('entityable', function () {
                return [
                    'id'          => $this->entityable->id,
                    'title'       => $this->entityable->title,
                    'description' => $this->entityable->description,
                ];
            }),
            'created_at' => $this->created_at,
        ];
    }
}
