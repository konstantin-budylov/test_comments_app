<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityTypes;
use App\Http\Controllers\Api\ApiController as Controller;
use App\Http\Requests\CreateEntityRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class EntitiesController extends Controller
{

    /**
     *  List all entities
     * @group Entities
     *
     * @queryParam entitiesCursor string The cursor for pagination. Example: eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
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
     * @queryParam commentsCursor string The cursor for pagination of comments. Example: eyJpZCI6MiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjIyOjQ5LjAwMDAwMFoifQ==
     * @param int $entity_id
     * @return JsonResponse
     */
    public function view(Request $request, $entity_id)
    {
        try {
            $entity = Entity::findOrFail($entity_id);

            if (! $entity->entityable) {
                abort(404, 'Related entity not found');
            }

            $cursorName = 'commentsCursor';
            $this->valiateCursor($request, $cursorName);

            $comments = $entity->comments()
                ->with('children')
                ->cursorPaginate(
                    perPage: parent::DEFAULT_PER_PAGE,
                    cursorName: $cursorName
                );

            return response()->json([
                'data' => [
                    'entity' => new EntityResource($entity),
                    'comments' => CommentResource::collection($comments->items()),
                ],
                'meta' => [
                    'next_cursor' => optional($comments->nextCursor())->encode(),
                    'prev_cursor' => optional($comments->previousCursor())->encode(),
                    'per_page' => $comments->perPage(),
                ],
            ]);

        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'entity_not_found',
                'message' => 'entity not found'
            ], 404);
        }
    }

    /**
     * Create new entity
     *
     * @group Entities
     * @bodyParam entity_type int required The type of the entity. Example: 1 - News, 2 - VideoPost
     * @bodyParam title string required The title of the entity. Example: Sample Title
     * @bodyParam description text required The description of the entity. Example: This is a description
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
