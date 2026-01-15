<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController as Controller;
use App\Http\Requests\DeleteCommentRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Entity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller
{
    /**
     * List all entity comments
     * @group Comments
     * @return JsonResponse
     */
    public function index($entity_id)
    {
        try {
            $entity = Entity::findOrFail($entity_id);

            if (! $entity->entityable) {
                abort(404, 'Related entity not found');
            }

            $comments = Comment::where('entity_id', $entity->id)
                ->orderBy('path')
                ->get([
                    'id',
                    'entity_id',
                    'user_id',
                    'parent_id',
                    'text',
                    'path',
                    'created_at',
                ]);

            return response()->json([
                'data' => $this->buildTree($comments),
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'entity_not_found',
                'message' => 'entity not found'
            ], 404);
        }
    }

    /**
     * Create entity comment
     * @group Comments
     *
     * @bodyParam user_id integer required user_id. Example: 1
     * @bodyParam text string required comment text. Example: This is a comment.
     * @bodyParam parent_id integer parent comment id. Example: 2
     * @param int $entity_id
     * @param StoreCommentRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function create(int $entity_id, StoreCommentRequest $request): JsonResponse
    {
        try {
            $entity = Entity::findOrFail($entity_id);

            if (! $entity->entityable) {
                abort(404, 'Related entity not found');
            }

            $parentId = $request->parent_id;

            if ($parentId) {
                $parent = Comment::where('id', $parentId)
                    ->where('entity_id', $entity->id)
                    ->first();

                if (! $parent) {
                    return response()->json([
                        'error' => 'invalid_parent',
                        'message' => 'parent comment not found or does not belong to this entity'
                    ], 422);
                }
            }

            return DB::transaction(function () use ($entity, $parentId, $request) {
                $comment = Comment::create([
                    'entity_id' => $entity->id,
                    'user_id'   => $request->user_id,
                    'parent_id' => $parentId,
                    'text'      => $request->text,
                ]);

                return response()->json([
                    'data' => new CommentResource($comment),
                ], 201);
            });

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'entity_not_found',
                'message' => 'entity not found'
            ], 404);
        }
    }

    /**
     * Update comment
     * @group Comments
     *
     * @bodyParam text string required New text for the comment. Example: Updated comment text.
     * @bodyParam user_id integer required ID of the user attempting to update the comment.
     * @param Comment $comment
     * @param UpdateCommentRequest $request
     * @return JsonResponse
     */
    public function update(Comment $comment, UpdateCommentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ((int) $comment->user_id !== (int) $data['user_id']) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'User has no permission to update this comment',
            ], 403);
        }

        return DB::transaction(function () use ($comment, $data) {
            $comment->update([
                'text' => $data['text'],
            ]);

            return response()->json([
                'data' => new CommentResource($comment),
            ]);
        });
    }

    /**
     * Delete comment
     * @group Comments
     * @param Comment $comment
     * @param DeleteCommentRequest $request
     * @return JsonResponse
     */
    public function destroy(Comment $comment, DeleteCommentRequest $request): JsonResponse
    {
        if ((int) $comment->user_id !== (int) $request->validated()['user_id']) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'User has no permission to delete this comment',
            ], 403);
        }

        $hasChildren = Comment::where('parent_id', $comment->id)->exists();

        return DB::transaction(function () use ($comment, $hasChildren) {
            if ($hasChildren) {
                $comment->update([
                    'text' => 'Comment has been deleted',
                ]);

                return response()->json([
                    'data' => [
                        'id' => $comment->id,
                        'deleted' => true,
                        'soft' => true,
                    ],
                ]);
            }

            $comment->delete();

            return response()->json([
                'data' => [
                    'id' => $comment->id,
                    'deleted' => true,
                    'soft' => false,
                ],
            ]);
        });
    }

    private function buildTree(Collection $comments): array
    {
        $items = [];
        $tree  = [];

        foreach ($comments as $comment) {
            $items[$comment->id] = [
                'id'        => $comment->id,
                'user_id'   => $comment->user_id,
                'text'      => $comment->text,
                'created_at'=> $comment->created_at,
                'children'  => [],
            ];
        }

        foreach ($comments as $comment) {
            if ($comment->parent_id) {
                $items[$comment->parent_id]['children'][] = &$items[$comment->id];
            } else {
                $tree[] = &$items[$comment->id];
            }
        }

        return $tree;
    }
}
