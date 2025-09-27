<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Получить список товаров текущего пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Product::with(['seller', 'user', 'claims'])
            ->where('user_id', $user->id);

        // Фильтрация по продавцу
        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        // Фильтрация по претензии
        if ($request->filled('claim_id')) {
            $query->where('claim_id', $request->claim_id);
        }

        // Поиск по названию или модели
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products),
            'message' => 'Товары успешно получены'
        ]);
    }

    /**
     * Получить конкретный товар текущего пользователя
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        $product = Product::with(['seller', 'user'])
            ->where('user_id', $user->id)
            ->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден или не принадлежит вам'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product),
            'message' => 'Товар успешно получен'
        ]);
    }

    /**
     * Создать новый товар
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'serial_number' => 'nullable|string|max:255|unique:products,serial_number,NULL,id,deleted_at,NULL',
                'price' => 'required|numeric|min:0',
                'date_of_buying' => 'required|date|before_or_equal:today',
                'warranty_period' => 'nullable|integer|min:1|max:120',
                'seller_id' => 'required|uuid|exists:sellers,id',
                'claim_id' => 'nullable|uuid'
            ]);

            // Добавляем user_id текущего пользователя
            $validated['user_id'] = auth()->id();

            $product = Product::create($validated);
            $product->load(['seller', 'user']);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Товар успешно создан'
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Обновить товар текущего пользователя
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        $product = Product::where('user_id', $user->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден или не принадлежит вам'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'model' => 'sometimes|required|string|max:255',
                'serial_number' => 'nullable|string|max:255|unique:products,serial_number,' . $id . ',id,deleted_at,NULL',
                'price' => 'sometimes|required|numeric|min:0',
                'date_of_buying' => 'sometimes|required|date|before_or_equal:today',
                'warranty_period' => 'nullable|integer|min:1|max:120',
                'seller_id' => 'sometimes|required|uuid|exists:sellers,id',
                'claim_id' => 'nullable|uuid'
            ]);

            $product->update($validated);
            $product->load(['seller']);

            return response()->json([
                'success' => true,
                'data' => new ProductResource($product),
                'message' => 'Товар успешно обновлен'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Удалить товар текущего пользователя
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        $product = Product::where('user_id', $user->id)->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден или не принадлежит вам'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Товар успешно удален'
        ]);
    }

    /**
     * Получить товары конкретного продавца
     */
    public function getBySeller(string $sellerId): JsonResponse
    {
        $seller = User::find($sellerId);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Продавец не найден'
            ], 404);
        }

        $products = Product::with(['seller'])
            ->where('seller_id', $sellerId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'seller' => $seller,
                'products' => ProductResource::collection($products)
            ],
            'message' => 'Товары продавца успешно получены'
        ]);
    }

    /**
     * Получить статистику по товарам текущего пользователя
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();
        $userProducts = Product::where('user_id', $user->id);
        
        $stats = [
            'total_products' => $userProducts->count(),
            'total_value' => $userProducts->sum('price'),
            'average_price' => $userProducts->avg('price'),
            'products_with_serial' => $userProducts->whereNotNull('serial_number')->count(),
            'products_without_serial' => $userProducts->whereNull('serial_number')->count(),
            'recent_products' => $userProducts->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Статистика успешно получена'
        ]);
    }
}
