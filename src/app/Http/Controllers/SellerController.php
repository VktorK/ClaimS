<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Http\Resources\SellerResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class SellerController extends Controller
{
    /**
     * Получить список всех продавцов
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Seller::with(['products'])
            ->where('user_id', $user->id);

        // Поиск по названию или ОГРН
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ogrn', 'like', "%{$search}%");
            });
        }

        // Фильтрация по наличию ОГРН
        if ($request->filled('has_ogrn')) {
            if ($request->has_ogrn) {
                $query->whereNotNull('ogrn');
            } else {
                $query->whereNull('ogrn');
            }
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $sellers = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SellerResource::collection($sellers),
            'message' => 'Продавцы успешно получены'
        ]);
    }

    /**
     * Получить конкретного продавца
     */
    public function show(string $id): JsonResponse
    {
        $seller = Seller::with(['products'])->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Продавец не найден'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SellerResource($seller),
            'message' => 'Продавец успешно получен'
        ]);
    }

    /**
     * Создать нового продавца
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'address' => 'required|string',
                'ogrn' => [
                    'required',
                    'string',
                    'max:15',
                    function ($attribute, $value, $fail) use ($user) {
                        $exists = Seller::where('user_id', $user->id)
                            ->where('ogrn', $value)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Продавец с таким ОГРН уже существует в вашем списке.');
                        }
                    }
                ],
            ]);

            $validated['user_id'] = $user->id;

            $seller = Seller::create($validated);
            $seller->load(['products']);

            return response()->json([
                'success' => true,
                'data' => new SellerResource($seller),
                'message' => 'Продавец успешно создан'
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
     * Обновить продавца
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        $seller = Seller::where('user_id', $user->id)->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Продавец не найден'
            ], 404);
        }

        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'address' => 'sometimes|required|string',
                'ogrn' => [
                    'sometimes',
                    'required',
                    'string',
                    'max:15',
                    function ($attribute, $value, $fail) use ($user, $id) {
                        $exists = Seller::where('user_id', $user->id)
                            ->where('ogrn', $value)
                            ->where('id', '!=', $id)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Продавец с таким ОГРН уже существует в вашем списке.');
                        }
                    }
                ],
            ]);

            $seller->update($validated);
            $seller->load(['products']);

            return response()->json([
                'success' => true,
                'data' => new SellerResource($seller),
                'message' => 'Продавец успешно обновлен'
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
     * Удалить продавца
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        $seller = Seller::where('user_id', $user->id)->find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Продавец не найден'
            ], 404);
        }

        // Проверяем, есть ли связанные товары
        if ($seller->products()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить продавца, у которого есть товары'
            ], 422);
        }

        $seller->delete();

        return response()->json([
            'success' => true,
            'message' => 'Продавец успешно удален'
        ]);
    }

    /**
     * Получить статистику по продавцам
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();
        
        $stats = [
            'total_sellers' => Seller::where('user_id', $user->id)->count(),
            'sellers_with_ogrn' => Seller::where('user_id', $user->id)->whereNotNull('ogrn')->count(),
            'sellers_without_ogrn' => Seller::where('user_id', $user->id)->whereNull('ogrn')->count(),
            'recent_sellers' => Seller::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(30))->count(),
            'top_sellers_by_products' => Seller::where('user_id', $user->id)->withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($seller) {
                    return [
                        'id' => $seller->id,
                        'title' => $seller->title,
                        'products_count' => $seller->products_count,
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Статистика успешно получена'
        ]);
    }

    /**
     * Получить товары конкретного продавца
     */
    public function getProducts(string $id): JsonResponse
    {
        $seller = Seller::find($id);

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Продавец не найден'
            ], 404);
        }

        $products = $seller->products()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'seller' => new SellerResource($seller),
                'products' => $products
            ],
            'message' => 'Товары продавца успешно получены'
        ]);
    }
}
