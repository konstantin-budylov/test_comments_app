<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EntityTypes;
use App\Http\Controllers\Api\ApiController as Controller;
use App\Http\Resources\EntityResource;
use App\Models\Entity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;

class NewsController extends Controller
{
    /**
     * List all news entities
     *
     * @group News
     *
     * @queryParam newsCursor string The cursor for pagination. Example: eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cursorName = 'newsCursor';

        $this->valiateCursor($request, $cursorName);

        $entities = Entity::with('entityable')
            ->ofType(EntityTypes::NEWS)
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
