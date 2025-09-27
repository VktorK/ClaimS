<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimTemplateResource;
use App\Models\ClaimTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimTemplateController extends Controller
{
    /**
     * Получить список шаблонов пользователя
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $query = ClaimTemplate::where('user_id', $user->id);

            // Фильтр по активности
            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Поиск по названию
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $templates = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => ClaimTemplateResource::collection($templates),
                'message' => 'Шаблоны успешно получены'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении шаблонов: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Создать новый шаблон
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'template_content' => 'required|string',
                'is_active' => 'boolean',
            ]);

            $template = ClaimTemplate::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'template_content' => $validated['template_content'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'data' => new ClaimTemplateResource($template),
                'message' => 'Шаблон успешно создан'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить конкретный шаблон
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $template = ClaimTemplate::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new ClaimTemplateResource($template),
                'message' => 'Шаблон успешно получен'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновить шаблон
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $template = ClaimTemplate::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон не найден'
                ], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'template_content' => 'sometimes|required|string',
                'is_active' => 'sometimes|boolean',
            ]);

            $template->update($validated);

            return response()->json([
                'success' => true,
                'data' => new ClaimTemplateResource($template),
                'message' => 'Шаблон успешно обновлен'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удалить шаблон
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $template = ClaimTemplate::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон не найден'
                ], 404);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Шаблон успешно удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Рендерить шаблон с данными
     */
    public function render(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            $template = ClaimTemplate::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Шаблон не найден'
                ], 404);
            }

            $validated = $request->validate([
                'data' => 'required|array',
            ]);

            $renderedContent = $template->render($validated['data']);

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $renderedContent,
                    'template' => new ClaimTemplateResource($template)
                ],
                'message' => 'Шаблон успешно отрендерен'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при рендеринге шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получить доступные плейсхолдеры
     */
    public function placeholders(): JsonResponse
    {
        try {
            $placeholders = ClaimTemplate::getAvailablePlaceholders();

            return response()->json([
                'success' => true,
                'data' => $placeholders,
                'message' => 'Плейсхолдеры успешно получены'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при получении плейсхолдеров: ' . $e->getMessage()
            ], 500);
        }
    }
}
