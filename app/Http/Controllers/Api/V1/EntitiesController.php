<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityTypes;
use App\Http\Controllers\Api\ApiController as Controller;
use App\Http\Requests\CreateEntityRequest;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Facades\DB;
use Throwable;

class EntitiesController extends Controller
{

    /**
     *  List all entities
     * @group Entities
     *
     * @queryParam entitiesCursor string The cursor for pagination. Example: eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
     * @response 200 scenario="Successful response" {
     *      "data": [
     *      {
     *          "id": 6,
     *          "entityable_type": "news",
     *          "entityable_id": 5,
     *          "created_at": "2026-01-15T12:34:42.000000Z",
     *          "data": {
     *              "id": 5,
     *              "title": "Sample Title",
     *              "description": "This is a"
     *          }
     *      }
     *      ],
     *      "meta": {
     *          "next_cursor": null,
     *          "prev_cursor": null,
     *          "per_page": 20
     *      }
     * }"
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cursorName = 'entitiesCursor';

        $this->valiateCursor($request, $cursorName);

        $entities = Entity::with('entityable')
            ->orderBy('entities.created_at', 'desc')
            ->cursorPaginate(
                perPage: parent::DEFAULT_PER_PAGE,
                cursorName: $cursorName,
            );

        return response()->json([
            'data' => EntityResource::collection($entities->items()),
            'meta' => [
                'next_cursor' => optional($entities->nextCursor())->encode(),
                'prev_cursor' => optional($entities->previousCursor())->encode(),
                'per_page'    => $entities->perPage(),
            ],
        ]);
    }

    /**
     * Show single entity by id
     * @group Entities
     *
     * @param int $entity_id
     * @response 200 scenario="Successful response" {
     * "data": {
     * "entity_id": 1,
     * "entity_type": "news",
     * "entity_data": {
     * "id": 1,
     * "title": "Sample Title",
     * "description": "This is a news"
     * },
     * "created_at": "2026-01-15T12:22:49.000000Z"
     * }
     * }
     *
     * @return JsonResponse
     */
    public function view(int $entity_id)
    {
        $entity = Entity::with([
            'entityable' => function ($query) {
                $query->select(['id', 'title', 'description']);
            }
        ])->findOrFail($entity_id);

        if (! $entity?->entityable) {
            return response()->json([
                'error' => 'entityable_not_found',
                'message' => 'Related entity not found'
            ], 404);
        }

        return response()->json([
            'data' => new EntityResource($entity),
        ]);
    }

    /**
     * Create new entity
     *
     * @group Entities
     * @bodyParam entity_type int required The type of the entity. Example: 1 - News, 2 - VideoPost
     * @bodyParam title string required The title of the entity. Example: Sample Title
     * @bodyParam description string required The description of the entity. Example: This is a
     *
     * @response 201 scenario="Entity created successfully" {"entity_id": 1,"entity_type": 1,"data": {"id": 1,"title": "Sample Title","description": "This is a sample description","created_at": "2024-01-01T00:00:00.000000Z""}}
     * @response 422 scenario="Validation error" {"message": "The given data was invalid.","errors": {"entity_type": ["The selected entity type is invalid."]}}
     *
     * @return JsonResponse
     */
    public function create(CreateEntityRequest $request)
    {
        $data = $request->validated();

        $entityType = EntityTypes::from($data['entity_type']);
        $modelClass = $entityType->modelClass();

        try {
            return DB::transaction(function () use ($entityType, $modelClass, $data) {
                /** @var Model $model */
                $model = $modelClass::create([
                    'title'       => $data['title'],
                    'description' => $data['description'],
                ]);

                $entity = $model->entity()->create([
                    'entityable_type' => $entityType,
                ]);

                return response()->json([
                    'entity_id'   => $entity->id,
                    'entity_type' => $entityType->label(),
                    'data'        => $model->only(['id', 'title', 'description', 'created_at'])
                ], 201);
            });
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Failed to create entity',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
