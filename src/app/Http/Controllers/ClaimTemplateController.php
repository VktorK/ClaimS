<?php

namespace App\Http\Controllers;

use App\Models\ClaimTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ClaimTemplateResource;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class ClaimTemplateController extends Controller
{
    /**
     * Получить список шаблонов пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        $query = ClaimTemplate::where('user_id', $user->id);
        
        // Фильтр по активности
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }
        
        // Поиск по названию или описанию
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $templates = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => ClaimTemplateResource::collection($templates)
        ]);
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
                'description' => 'nullable|string|max:1000',
                'template_content' => 'required|string',
                'is_active' => 'boolean'
            ]);

            $validated['user_id'] = $user->id;
            $validated['is_active'] = $validated['is_active'] ?? true;

            $template = ClaimTemplate::create($validated);

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
        }
    }

    /**
     * Получить шаблон по ID
     */
    public function show(string $id): JsonResponse
    {
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
            'data' => new ClaimTemplateResource($template)
        ]);
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'template_content' => 'required|string',
                'is_active' => 'boolean'
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
        }
    }

    /**
     * Удалить шаблон
     */
    public function destroy(string $id): JsonResponse
    {
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
    }

    /**
     * Получить доступные плейсхолдеры для шаблонов
     */
    public function placeholders(): JsonResponse
    {
        $placeholders = [
            'consumer' => [
                'consumer.full_name' => 'Полное имя потребителя',
                'consumer.short_name' => 'Краткое имя потребителя',
                'consumer.address' => 'Адрес потребителя',
                'consumer.passport' => 'Паспорт потребителя',
                'consumer.formatted_passport' => 'Паспорт потребителя (форматированный)',
                'consumer.inn' => 'ИНН потребителя',
                'consumer.formatted_inn' => 'ИНН потребителя (форматированный)',
                'consumer.passport_issued_by' => 'Паспорт выдан',
                'consumer.passport_issue_date' => 'Дата выдачи паспорта',
            ],
            'seller' => [
                'seller.title' => 'Название продавца',
                'seller.short_title' => 'Краткое название продавца',
                'seller.address' => 'Адрес продавца',
                'seller.inn' => 'ИНН продавца',
                'seller.formatted_inn' => 'ИНН продавца (форматированный)',
                'seller.ogrn' => 'ОГРН продавца',
                'seller.formatted_ogrn' => 'ОГРН продавца (форматированный)',
                'seller.phone' => 'Телефон продавца',
                'seller.email' => 'Email продавца',
            ],
            'product' => [
                'product.title' => 'Название товара',
                'product.model' => 'Модель товара',
                'product.serial_number' => 'Серийный номер товара',
                'product.price' => 'Цена товара',
                'product.date_of_buying' => 'Дата покупки товара',
                'product.warranty_period' => 'Срок гарантии (месяцы)',
                'product.warranty_end_date' => 'Дата окончания гарантии',
            ],
            'claim' => [
                'claim.type' => 'Тип претензии',
                'claim.current_defect' => 'Текущий недостаток',
                'claim.previous_repair' => 'Предыдущий ремонт',
                'claim.repair_document' => 'Документ о ремонте',
                'claim.previous_defect' => 'Предыдущий недостаток',
                'claim.expertise_conducted' => 'Проводилась ли экспертиза',
                'claim.expertise_data' => 'Данные экспертизы',
                'claim.expertise_defect' => 'Недостаток по экспертизе',
                'claim.real_defect' => 'Реальный недостаток',
                'claim.claimed_amount' => 'Заявленная сумма',
                'claim.claim_date' => 'Дата претензии',
            ],
            'system' => [
                'current_date' => 'Текущая дата',
                'current_time' => 'Текущее время',
                'user.name' => 'Имя пользователя',
                'user.email' => 'Email пользователя',
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $placeholders
        ]);
    }

    /**
     * Скачать шаблон в формате DOCX
     */
    public function download(string $id)
    {
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

        try {
            // Создаем простой DOCX файл
            $docxContent = $this->createDocxFile($template);
            
            $fileName = $this->sanitizeFileName($template->name) . '.docx';
            
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
     * Создать DOCX файл
     */
    private function createDocxFile(ClaimTemplate $template): string
    {
        // Создаем временный ZIP архив
        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        
        // Удаляем временный файл и создаем новый
        unlink($tempFile);
        $tempFile .= '.zip';
        
        if ($zip->open($tempFile, ZipArchive::CREATE) !== TRUE) {
            throw new \Exception('Не удалось создать ZIP архив');
        }

        // Добавляем необходимые файлы для DOCX
        $this->addDocxFiles($zip, $template);
        
        $zip->close();
        
        // Читаем содержимое файла
        $content = file_get_contents($tempFile);
        
        // Удаляем временный файл
        unlink($tempFile);
        
        return $content;
    }

    /**
     * Добавить файлы в DOCX архив
     */
    private function addDocxFiles(ZipArchive $zip, ClaimTemplate $template): void
    {
        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
    <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
</Types>';
        
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
        
        $zip->addFromString('_rels/.rels', $rels);

        // word/_rels/document.xml.rels
        $wordRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        
        $zip->addFromString('word/_rels/document.xml.rels', $wordRels);

        // word/document.xml
        $document = $this->createDocumentXml($template);
        $zip->addFromString('word/document.xml', $document);

        // word/styles.xml
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:style w:type="paragraph" w:styleId="Normal">
        <w:name w:val="Normal"/>
        <w:qFormat/>
    </w:style>
    <w:style w:type="paragraph" w:styleId="Heading1">
        <w:name w:val="heading 1"/>
        <w:basedOn w:val="Normal"/>
        <w:qFormat/>
        <w:pPr>
            <w:pBdr>
                <w:bottom w:val="single" w:sz="6" w:space="1" w:color="auto" w:themeColor="accent1"/>
            </w:pBdr>
        </w:pPr>
        <w:rPr>
            <w:b/>
            <w:sz w:val="32"/>
        </w:rPr>
    </w:style>
</w:styles>';
        
        $zip->addFromString('word/styles.xml', $styles);

        // docProps/core.xml
        $core = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:title>' . htmlspecialchars($template->name) . '</dc:title>
    <dc:creator>ClaimS System</dc:creator>
    <cp:lastModifiedBy>ClaimS System</cp:lastModifiedBy>
    <dcterms:created xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:modified>
</cp:coreProperties>';
        
        $zip->addFromString('docProps/core.xml', $core);

        // docProps/app.xml
        $app = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
    <Application>ClaimS System</Application>
    <DocSecurity>0</DocSecurity>
    <ScaleCrop>false</ScaleCrop>
    <SharedDoc>false</SharedDoc>
    <HyperlinksChanged>false</HyperlinksChanged>
    <AppVersion>1.0</AppVersion>
</Properties>';
        
        $zip->addFromString('docProps/app.xml', $app);
    }

    /**
     * Создать XML содержимое документа
     */
    private function createDocumentXml(ClaimTemplate $template): string
    {
        // Очищаем содержимое от HTML тегов и заменяем &nbsp; на пробелы
        $content = $this->cleanTemplateContent($template->template_content);
        $name = htmlspecialchars($template->name, ENT_XML1, 'UTF-8');
        $description = htmlspecialchars($template->description ?? '', ENT_XML1, 'UTF-8');
        
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:body>
        <w:p>
            <w:pPr>
                <w:pStyle w:val="Heading1"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:b/>
                    <w:sz w:val="32"/>
                </w:rPr>
                <w:t>' . $name . '</w:t>
            </w:r>
        </w:p>';
        
        if ($description) {
            $document .= '
        <w:p>
            <w:r>
                <w:t>' . $description . '</w:t>
            </w:r>
        </w:p>';
        }
        
        // Разбиваем содержимое на параграфы по переносам строк
        $paragraphs = explode("\n", $content);
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $paragraph = htmlspecialchars($paragraph, ENT_XML1, 'UTF-8');
                $document .= '
        <w:p>
            <w:r>
                <w:t>' . $paragraph . '</w:t>
            </w:r>
        </w:p>';
            } else {
                // Пустой параграф для отступа
                $document .= '
        <w:p>
            <w:r>
                <w:t></w:t>
            </w:r>
        </w:p>';
            }
        }
        
        $document .= '
    </w:body>
</w:document>';
        
        return $document;
    }

    /**
     * Очистить содержимое шаблона от HTML тегов и лишних символов
     */
    private function cleanTemplateContent(string $content): string
    {
        // Сначала заменяем HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Специальная обработка неразрывных пробелов (UTF-8)
        $content = str_replace("\xC2\xA0", ' ', $content); // UTF-8 неразрывный пробел
        $content = str_replace('&nbsp;', ' ', $content);
        $content = str_replace('&#160;', ' ', $content);
        $content = str_replace('&#xA0;', ' ', $content);
        
        // Заменяем HTML теги на переносы строк
        $content = preg_replace('/<div[^>]*>/i', "\n", $content);
        $content = preg_replace('/<\/div>/i', "\n", $content);
        $content = preg_replace('/<br\s*\/?>/i', "\n", $content);
        $content = preg_replace('/<p[^>]*>/i', "\n", $content);
        $content = preg_replace('/<\/p>/i', "\n", $content);
        
        // Удаляем все остальные HTML теги
        $content = strip_tags($content);
        
        // Заменяем множественные пробелы на одинарные (включая неразрывные пробелы)
        $content = preg_replace('/\s+/', ' ', $content);
        
        // Убираем лишние переносы строк
        $content = preg_replace('/\n\s*\n/', "\n", $content);
        
        // Убираем пробелы в начале и конце строк
        $lines = explode("\n", $content);
        $lines = array_map('trim', $lines);
        
        // Убираем пустые строки
        $lines = array_filter($lines, function($line) {
            return !empty($line);
        });
        
        // Дополнительная очистка: убираем лишние пробелы между словами
        $cleanedLines = [];
        foreach ($lines as $line) {
            // Убираем множественные пробелы между словами
            $line = preg_replace('/\s+/', ' ', $line);
            // Убираем пробелы в начале и конце
            $line = trim($line);
            if (!empty($line)) {
                $cleanedLines[] = $line;
            }
        }
        
        return implode("\n", $cleanedLines);
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
        
        return $fileName ?: 'template';
    }

    /**
     * Рендеринг шаблона с подстановкой переменных для претензии
     */
    public function render(Request $request, string $id): JsonResponse
    {
        $user = auth()->user();
        
        // Получаем шаблон
        $template = ClaimTemplate::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Шаблон не найден'
            ], 404);
        }

        // Получаем данные претензии из запроса
        $claimData = $request->validate([
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

        try {
            // Создаем временную претензию для рендеринга
            $tempClaim = new \App\Models\Claim();
            $tempClaim->fill($claimData);
            $tempClaim->user_id = $user->id;
            $tempClaim->template_id = $template->id;
            
            // Загружаем связанные данные
            $product = \App\Models\Product::where('id', $claimData['product_id'])
                ->where('user_id', $user->id)
                ->with(['seller', 'consumer'])
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Товар не найден или не принадлежит вам'
                ], 404);
            }

            $tempClaim->setRelation('product', $product);
            $tempClaim->setRelation('template', $template);
            $tempClaim->setRelation('user', $user);

            // Рендерим содержимое шаблона
            $renderedContent = $this->renderTemplateContent($tempClaim);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'rendered_content' => $renderedContent,
                    'template_name' => $template->name,
                    'template_id' => $template->id
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
     * Рендеринг содержимого шаблона с подстановкой переменных
     */
    private function renderTemplateContent(\App\Models\Claim $claim): string
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
    private function prepareTemplateData(\App\Models\Claim $claim): array
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
}