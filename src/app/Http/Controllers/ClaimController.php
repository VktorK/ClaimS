<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Models\Claim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    /**
     * Получить список претензий
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = Claim::with(['product', 'user', 'template'])
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
                'template_id' => 'nullable|uuid|exists:claim_templates,id',
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
            $claim->load(['product', 'user', 'template']);

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
        
        $claim = Claim::with(['product', 'user', 'template'])
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
                'template_id' => 'nullable|uuid|exists:claim_templates,id',
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
            $claim->load(['product', 'user', 'template']);

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

        $claims = Claim::with(['product', 'user', 'template'])
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

    /**
     * Рендеринг шаблона претензии с подстановкой переменных
     */
    public function renderTemplate(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        
        $claim = Claim::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['product.seller', 'product.consumer', 'template'])
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Претензия не найдена'
            ], 404);
        }

        if (!$claim->template) {
            return response()->json([
                'success' => false,
                'message' => 'Шаблон не выбран для данной претензии'
            ], 400);
        }

        try {
            $renderedContent = $this->renderTemplateContent($claim);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rendered_content' => $renderedContent,
                    'template_name' => $claim->template->name,
                    'claim_id' => $claim->id
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при рендеринге шаблона: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Скачать готовую претензию в формате DOCX
     */
    public function downloadClaim(Request $request, string $id)
    {
        $user = auth()->user();
        
        $claim = Claim::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['product.seller', 'product.consumer', 'template'])
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Претензия не найдена'
            ], 404);
        }

        if (!$claim->template) {
            return response()->json([
                'success' => false,
                'message' => 'Шаблон не выбран для данной претензии'
            ], 400);
        }

        try {
            // Рендерим содержимое шаблона
            $renderedContent = $this->renderTemplateContent($claim);
            
            // Создаем временный шаблон с отрендеренным содержимым
            $tempTemplate = new \App\Models\ClaimTemplate();
            $tempTemplate->name = $claim->template->name . ' - ' . $claim->product->title;
            $tempTemplate->description = 'Сгенерированная претензия от ' . now()->format('d.m.Y H:i');
            $tempTemplate->template_content = $renderedContent;
            
            // Используем метод из ClaimTemplateController для создания DOCX
            $templateController = new \App\Http\Controllers\ClaimTemplateController();
            $reflection = new \ReflectionClass($templateController);
            $method = $reflection->getMethod('createDocxFile');
            $method->setAccessible(true);
            
            $docxContent = $method->invoke($templateController, $tempTemplate);
            
            $fileName = $this->sanitizeFileName($claim->template->name . '_' . $claim->product->title) . '.docx';
            
            return response($docxContent)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Content-Length', strlen($docxContent));
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при создании файла: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Рендеринг содержимого шаблона с подстановкой переменных
     */
    private function renderTemplateContent(Claim $claim): string
    {
        $content = $claim->template->template_content;
        
        // Подготавливаем данные для подстановки
        $data = $this->prepareTemplateData($claim);
        
        // Заменяем плейсхолдеры на данные
        foreach ($data as $placeholder => $value) {
            $content = str_replace('{{' . $placeholder . '}}', $value ?? '', $content);
        }
        
        return $content;
    }

    /**
     * Подготовка данных для подстановки в шаблон
     */
    private function prepareTemplateData(Claim $claim): array
    {
        $product = $claim->product;
        $seller = $product->seller;
        $consumer = $product->consumer;
        
        return [
            // Данные потребителя
            'consumer.full_name' => $consumer ? $consumer->full_name : '',
            'consumer.short_name' => $consumer ? $consumer->short_name : '',
            'consumer.address' => $consumer ? $consumer->address : '',
            'consumer.passport' => $consumer ? $consumer->passport : '',
            'consumer.formatted_passport' => $consumer ? $this->formatPassport($consumer->passport) : '',
            'consumer.inn' => $consumer ? $consumer->inn : '',
            'consumer.formatted_inn' => $consumer ? $this->formatInn($consumer->inn) : '',
            'consumer.passport_issued_by' => $consumer ? $consumer->passport_issued_by : '',
            'consumer.passport_issue_date' => $consumer ? $consumer->passport_issue_date : '',
            
            // Данные продавца
            'seller.title' => $seller ? $seller->title : '',
            'seller.short_title' => $seller ? $seller->short_title : '',
            'seller.address' => $seller ? $seller->address : '',
            'seller.inn' => $seller ? $seller->inn : '',
            'seller.formatted_inn' => $seller ? $this->formatInn($seller->inn) : '',
            'seller.ogrn' => $seller ? $seller->ogrn : '',
            'seller.formatted_ogrn' => $seller ? $this->formatOgrn($seller->ogrn) : '',
            'seller.phone' => $seller ? $seller->phone : '',
            'seller.email' => $seller ? $seller->email : '',
            
            // Данные товара
            'product.title' => $product->title,
            'product.model' => $product->model,
            'product.serial_number' => $product->serial_number,
            'product.price' => number_format($product->price, 0, ',', ' '),
            'product.date_of_buying' => $product->date_of_buying ? \Carbon\Carbon::parse($product->date_of_buying)->format('d.m.Y') : '',
            'product.warranty_period' => $product->warranty_period,
            'product.warranty_end_date' => $product->warranty_period ? 
                \Carbon\Carbon::parse($product->date_of_buying)->addMonths($product->warranty_period)->format('d.m.Y') : '',
            
            // Данные претензии
            'claim.type' => $claim->type,
            'claim.current_defect' => $claim->current_defect,
            'claim.previous_repair' => $claim->was_in_repair ? 'Да' : 'Нет',
            'claim.repair_document' => $claim->service_center_documents,
            'claim.previous_defect' => $claim->previous_defect,
            'claim.expertise_conducted' => $claim->expertiseConducted ? 'Да' : 'Нет',
            'claim.expertise_data' => $claim->expertiseData,
            'claim.expertise_defect' => $claim->expertiseDefect,
            'claim.real_defect' => $claim->actualDefect,
            'claim.claimed_amount' => $claim->claimed_amount ? number_format($claim->claimed_amount, 0, ',', ' ') : '',
            'claim.claim_date' => $claim->claim_date ? \Carbon\Carbon::parse($claim->claim_date)->format('d.m.Y') : '',
            
            // Системные данные
            'current_date' => now()->format('d.m.Y'),
            'current_time' => now()->format('H:i'),
            'user.name' => $claim->user->name,
            'user.email' => $claim->user->email,
        ];
    }

    /**
     * Форматирование паспорта
     */
    private function formatPassport(?string $passport): string
    {
        if (!$passport || strlen($passport) !== 10) {
            return $passport ?? '';
        }
        
        return substr($passport, 0, 4) . ' ' . substr($passport, 4);
    }

    /**
     * Форматирование ИНН
     */
    private function formatInn(?string $inn): string
    {
        if (!$inn) {
            return '';
        }
        
        if (strlen($inn) === 12) {
            return substr($inn, 0, 4) . ' ' . substr($inn, 4, 4) . ' ' . substr($inn, 8);
        }
        
        return $inn;
    }

    /**
     * Форматирование ОГРН
     */
    private function formatOgrn(?string $ogrn): string
    {
        if (!$ogrn) {
            return '';
        }
        
        if (strlen($ogrn) === 13) {
            return substr($ogrn, 0, 2) . ' ' . substr($ogrn, 2, 2) . ' ' . substr($ogrn, 4, 6) . ' ' . substr($ogrn, 10);
        }
        
        return $ogrn;
    }

    /**
     * Очистить имя файла от недопустимых символов
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Удаляем недопустимые символы
        $fileName = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '', $fileName);
        
        // Заменяем пробелы на подчеркивания
        $fileName = str_replace(' ', '_', $fileName);
        
        // Ограничиваем длину
        if (strlen($fileName) > 100) {
            $fileName = substr($fileName, 0, 100);
        }
        
        return $fileName ?: 'claim';
    }
}
