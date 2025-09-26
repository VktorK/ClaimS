<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;

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

// Публичные маршруты аутентификации
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Защищенные маршруты (требуют JWT токен)
Route::middleware('auth:api')->prefix('auth')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
});

// Маршруты для профилей
Route::middleware('auth:api')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::post('/avatar', [ProfileController::class, 'uploadAvatar']);
});

// Публичные профили
Route::get('profiles/{userId}/public', [ProfileController::class, 'publicProfile']);

// Тестовый защищенный маршрут
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Доступ разрешен',
        'data' => [
            'user' => $request->user()
        ]
    ]);
});

// Публичные API endpoints для Vue приложения
Route::get('/info', function () {
    return response()->json([
        'success' => true,
        'message' => 'Vue + Laravel API работает!',
        'data' => [
            'backend' => 'Laravel 12',
            'frontend' => 'Vue 3',
            'router' => 'Vue Router 4',
            'timestamp' => now()->toISOString()
        ]
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
