<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConsumerResource;
use App\Models\Consumer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConsumerController extends Controller
{
    /**
     * Получить список потребителей для текущего пользователя
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $query = Consumer::where('user_id', $user->id)
                ->with(['user']);

            // Поиск по имени
            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('middle_name', 'like', "%{$search}%")
                      ->orWhere('passport', 'like', "%{$search}%")
                      ->orWhere('inn', 'like', "%{$search}%");
                });
            }

            $consumers = $query->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => ConsumerResource::collection($consumers)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении списка потребителей: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Создать нового потребителя
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'address' => 'required|string',
                'passport' => [
                    'required',
                    'string',
                    'size:10',
                    'regex:/^[0-9]{10}$/',
                    Rule::unique('consumers')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->whereNull('deleted_at');
                    })
                ],
                'passport_issued_by' => 'nullable|string|max:70',
                'passport_issued_date' => 'nullable|date|before_or_equal:today',
                'inn' => [
                    'nullable',
                    'string',
                    'size:12',
                    'regex:/^[0-9]{12}$/',
                    Rule::unique('consumers')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->whereNotNull('inn')->whereNull('deleted_at');
                    })
                ],
            ]);

            $consumer = Consumer::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                ...$validated
            ]);

            $consumer->load(['user']);

            return response()->json([
                'success' => true,
                'message' => 'Потребитель успешно создан',
                'data' => new ConsumerResource($consumer)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании потребителя: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить информацию о потребителе
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $consumer = Consumer::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['user'])
                ->first();

            if (!$consumer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Потребитель не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ConsumerResource($consumer)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении информации о потребителе: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновить информацию о потребителе
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $consumer = Consumer::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$consumer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Потребитель не найден'
                ], 404);
            }

            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'middle_name' => 'nullable|string|max:255',
                'address' => 'required|string',
                'passport' => [
                    'required',
                    'string',
                    'size:10',
                    'regex:/^[0-9]{10}$/',
                    Rule::unique('consumers')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->whereNull('deleted_at');
                    })->ignore($consumer->id)
                ],
                'passport_issued_by' => 'nullable|string|max:70',
                'passport_issued_date' => 'nullable|date|before_or_equal:today',
                'inn' => [
                    'nullable',
                    'string',
                    'size:12',
                    'regex:/^[0-9]{12}$/',
                    Rule::unique('consumers')->where(function ($query) use ($user) {
                        return $query->where('user_id', $user->id)->whereNotNull('inn')->whereNull('deleted_at');
                    })->ignore($consumer->id)
                ],
            ]);

            $consumer->update($validated);
            $consumer->load(['user']);

            return response()->json([
                'success' => true,
                'message' => 'Потребитель успешно обновлен',
                'data' => new ConsumerResource($consumer)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении потребителя: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удалить потребителя
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $consumer = Consumer::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$consumer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Потребитель не найден'
                ], 404);
            }

            $consumer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Потребитель успешно удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении потребителя: ' . $e->getMessage()
            ], 500);
        }
    }
}
