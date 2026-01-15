<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityTypes;
use App\Http\Controllers\Api\ApiController as Controller;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoPostController extends Controller
{
    /**
     * List all video post entities
     *
     * @group Video
     *
     * @queryParam videoPostCursor string The cursor for pagination. Example: eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
     * @response 200 scenario="Successful response" {
     *        "data": [{
     *            "id": 6,
     *            "entityable_type": "video",
     *            "entityable_id": 5,
     *            "created_at": "2026-01-15T12:34:42.000000Z",
     *            "data": {
     *                "id": 5,
     *                "title": "Sample Title",
     *                "description": "This is a"
     *            }
     *        }],
     *        "meta": {
     *            "next_cursor": null,
     *            "prev_cursor": null,
     *            "per_page": 20
     *        }
     *   }"
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cursorName = 'videoPostCursor';

        $this->valiateCursor($request, $cursorName);

        $entities = Entity::with('entityable')
            ->ofType(EntityTypes::VIDEO_POST)
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
}
