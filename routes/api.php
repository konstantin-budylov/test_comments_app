<?php

use App\Http\Controllers\Api\V1\EntitiesController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\VideoPostController;
use App\Http\Controllers\Api\V1\VideoPostsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api')->group(function () {
    // Define your API routes here
    Route::namespace('V1')->prefix('v1')->group(function () {

        Route::get('/health', 'HealthController@index');

        Route::prefix('entities')->group(function () {
            Route::get('/', [EntitiesController::class, 'index']);
            Route::get('/{entity_id}', [EntitiesController::class, 'view']);
            Route::post('/', [EntitiesController::class, 'create']);
            Route::post('/{entity_id}/comment', [\App\Http\Controllers\Api\V1\CommentsController::class, 'create']);
        });

        Route::prefix('news')->group(function () {
            Route::get('/', [NewsController::class, 'index']);
        });

        Route::prefix('video')->group(function () {
            Route::get('/', [VideoPostController::class, 'index']);
        });

        Route::prefix('comments')->group(function () {
            Route::post('/{comment_id}/update', [\App\Http\Controllers\Api\V1\CommentsController::class, 'update']);
            Route::post('/{comment_id}/delete', [\App\Http\Controllers\Api\V1\CommentsController::class, 'delete']);
        });

    });


});
