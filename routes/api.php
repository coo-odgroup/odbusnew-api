<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\JwtAuthController;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to the API'
    ]);
});



Route::middleware('api.key')->group(function () {
    Route::post('/v1/login', [JwtAuthController::class, 'login']);
    Route::post('/v1/refresh-token', [JwtAuthController::class, 'refresh']);
});

Route::middleware(['api.key', 'jwt.auth'])->group(function () {
    Route::post('/v1/logout', [JwtAuthController::class, 'logout']);
    Route::get('/v1/me', [JwtAuthController::class, 'me']);
    Route::post('/v1/getLocation', [ApiController::class, 'getLocation']);
    Route::post('/v1/getCities', [ApiController::class, 'getCities']);
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
