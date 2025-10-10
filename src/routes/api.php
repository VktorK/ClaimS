<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ClaimTemplateController;
use App\Http\Controllers\ConsumerController;
use App\Http\Controllers\ClaimController;

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

// Маршруты для продавцов
Route::middleware('auth:api')->prefix('sellers')->group(function () {
    Route::get('/', [SellerController::class, 'index']);
    Route::post('/', [SellerController::class, 'store']);
    Route::get('/statistics', [SellerController::class, 'statistics']);
    Route::get('/{id}/products', [SellerController::class, 'getProducts']);
    Route::get('/{id}', [SellerController::class, 'show']);
    Route::put('/{id}', [SellerController::class, 'update']);
    Route::delete('/{id}', [SellerController::class, 'destroy']);
});

// Маршруты для потребителей
Route::middleware('auth:api')->prefix('consumers')->group(function () {
    Route::get('/', [ConsumerController::class, 'index']);
    Route::post('/', [ConsumerController::class, 'store']);
    Route::get('/{id}', [ConsumerController::class, 'show']);
    Route::put('/{id}', [ConsumerController::class, 'update']);
    Route::delete('/{id}', [ConsumerController::class, 'destroy']);
});

// Маршруты для товаров
Route::middleware('auth:api')->prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/statistics', [ProductController::class, 'statistics']);
    Route::get('/seller/{sellerId}', [ProductController::class, 'getBySeller']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
});

// Маршруты для претензий
Route::middleware('auth:api')->prefix('claims')->group(function () {
    Route::get('/', [ClaimController::class, 'index']);
    Route::post('/', [ClaimController::class, 'store']);
    Route::get('/statistics', [ClaimController::class, 'statistics']);
    Route::get('/product/{productId}', [ClaimController::class, 'getClaimsByProduct']);
    Route::get('/{id}', [ClaimController::class, 'show']);
    Route::put('/{id}', [ClaimController::class, 'update']);
    Route::delete('/{id}', [ClaimController::class, 'destroy']);
    Route::get('/{id}/render', [ClaimController::class, 'renderTemplate']);
    Route::get('/{id}/download', [ClaimController::class, 'downloadClaim']);
});

// Маршруты для шаблонов претензий
Route::middleware('auth:api')->prefix('claim-templates')->group(function () {
    Route::get('/', [ClaimTemplateController::class, 'index']);
    Route::post('/', [ClaimTemplateController::class, 'store']);
    Route::get('/placeholders', [ClaimTemplateController::class, 'placeholders']);
    Route::get('/{id}', [ClaimTemplateController::class, 'show']);
    Route::put('/{id}', [ClaimTemplateController::class, 'update']);
    Route::delete('/{id}', [ClaimTemplateController::class, 'destroy']);
    Route::post('/{id}/render', [ClaimTemplateController::class, 'render']);
    Route::get('/{id}/download', [ClaimTemplateController::class, 'download']);
});

// Публичные маршруты для профилей
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
