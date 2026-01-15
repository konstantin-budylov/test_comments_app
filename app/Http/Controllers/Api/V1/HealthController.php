<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{

    /**
     * Healthcheck
     *
     * Check that the service is up. If everything is okay, you'll get a 200 OK response.
     *
     * Otherwise, the request will fail with a 400 error, and a response listing the failed services.
     *
     * @group Healthcheck
     * @response 400 scenario="Service is unhealthy" {"status": "failed"}
     * @responseField status The status of this API (`ok` or `failed`).
     */
    final public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }
}
