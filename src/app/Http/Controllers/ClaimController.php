<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Models\Claim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    /**
     * Получить список претензий
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Claim::with(['product', 'user'])
            ->where('user_id', $user->id);

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Фильтрация по товару
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Поиск по названию или описанию
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Пагинация
        $perPage = $request->get('per_page', 15);
        $claims = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ClaimResource::collection($claims),
            'message' => 'Претензии успешно получены'
        ]);
    }

    /**
     * Создать новую претензию
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'product_id' => 'required|uuid|exists:products,id',
                'type' => 'required|in:defect,warranty,return,complaint',
                'was_in_repair' => 'nullable|boolean',
                'service_center_documents' => 'nullable|string',
                'previous_defect' => 'nullable|string',
                'current_defect' => 'nullable|string',
                'expertiseConducted' => 'nullable|boolean',
                'expertiseData' => 'nullable|string',
                'expertiseDefect' => 'nullable|string',
                'actualDefect' => 'nullable|string',
                'claimed_amount' => 'nullable|numeric|min:0',
                'claim_date' => 'required|date',
            ]);

            // Проверяем, что товар принадлежит пользователю
            $product = \App\Models\Product::where('id', $validated['product_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден или не принадлежит вам'
                ], 404);
            }

            $validated['user_id'] = $user->id;
            $claim = Claim::create($validated);
            $claim->load(['product', 'user']);

            return response()->json([
                'success' => true,
                'data' => new ClaimResource($claim),
                'message' => 'Претензия успешно создана'
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
     * Получить конкретную претензию
     */
    public function show(string $id): JsonResponse
    {
        $user = auth()->user();
        
        $claim = Claim::with(['product', 'user'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Претензия не найдена'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ClaimResource($claim),
            'message' => 'Претензия успешно получена'
        ]);
    }

    /**
     * Обновить претензию
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $claim = Claim::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Претензия не найдена'
                ], 404);
            }

            $validated = $request->validate([
                'type' => 'required|in:defect,warranty,return,complaint',
                'status' => 'required|in:pending,in_progress,resolved,rejected',
                'was_in_repair' => 'nullable|boolean',
                'service_center_documents' => 'nullable|string',
                'previous_defect' => 'nullable|string',
                'current_defect' => 'nullable|string',
                'expertiseConducted' => 'nullable|boolean',
                'expertiseData' => 'nullable|string',
                'expertiseDefect' => 'nullable|string',
                'actualDefect' => 'nullable|string',
                'claimed_amount' => 'nullable|numeric|min:0',
                'claim_date' => 'required|date',
                'resolution_date' => 'nullable|date',
                'resolution_notes' => 'nullable|string',
            ]);

            $claim->update($validated);
            $claim->load(['product', 'user']);

            return response()->json([
                'success' => true,
                'data' => new ClaimResource($claim),
                'message' => 'Претензия успешно обновлена'
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
     * Удалить претензию
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth()->user();
        
        $claim = Claim::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Претензия не найдена'
            ], 404);
        }

        $claim->delete();

        return response()->json([
            'success' => true,
            'message' => 'Претензия успешно удалена'
        ]);
    }

    /**
     * Получить статистику претензий
     */
    public function statistics(): JsonResponse
    {
        $user = auth()->user();
        
        $stats = [
            'total' => Claim::where('user_id', $user->id)->count(),
            'pending' => Claim::where('user_id', $user->id)->where('status', 'pending')->count(),
            'in_progress' => Claim::where('user_id', $user->id)->where('status', 'in_progress')->count(),
            'resolved' => Claim::where('user_id', $user->id)->where('status', 'resolved')->count(),
            'rejected' => Claim::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Статистика претензий успешно получена'
        ]);
    }

    /**
     * Получить претензии товара
     */
    public function getClaimsByProduct(string $productId): JsonResponse
    {
        $user = auth()->user();
        
        // Проверяем, что товар принадлежит пользователю
        $product = \App\Models\Product::where('id', $productId)
            ->where('user_id', $user->id)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Товар не найден или не принадлежит вам'
            ], 404);
        }

        $claims = Claim::with(['product', 'user'])
            ->where('product_id', $productId)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => ClaimResource::collection($claims),
            'message' => 'Претензии товара успешно получены'
        ]);
    }
}
