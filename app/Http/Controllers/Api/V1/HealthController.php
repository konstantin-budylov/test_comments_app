<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController as Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{

    /**
     * Healthcheck
     *
     * @group Healthcheck
     */
    final public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
