<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClaimTemplate extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'template_content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Рендеринг шаблона с данными
     */
    public function render(array $data): string
    {
        $content = $this->template_content;
        
        // Заменяем плейсхолдеры на данные
        foreach ($data as $key => $value) {
            if (is_object($value) || is_array($value)) {
                // Обрабатываем объекты и массивы
                foreach ($value as $subKey => $subValue) {
                    $placeholder = "{{$key}.{$subKey}}";
                    $content = str_replace($placeholder, $subValue ?? '', $content);
                }
            } else {
                // Обрабатываем простые значения
                $placeholder = "{{$key}}";
                $content = str_replace($placeholder, $value ?? '', $content);
            }
        }
        
        return $content;
    }

    /**
     * Получить список доступных плейсхолдеров
     */
    public static function getAvailablePlaceholders(): array
    {
        return [
            'consumer' => [
                'full_name' => 'Полное имя потребителя',
                'short_name' => 'Краткое имя потребителя',
                'address' => 'Адрес потребителя',
                'passport' => 'Паспорт',
                'formatted_passport' => 'Форматированный паспорт',
                'inn' => 'ИНН',
                'formatted_inn' => 'Форматированный ИНН',
                'passport_issued_by' => 'Кем выдан паспорт',
                'passport_issued_date' => 'Дата выдачи паспорта',
            ],
            'product' => [
                'title' => 'Название товара',
                'model' => 'Модель товара',
                'serial_number' => 'Серийный номер',
                'price' => 'Цена товара',
                'date_of_buying' => 'Дата покупки',
                'warranty_period' => 'Срок гарантии',
            ],
            'seller' => [
                'title' => 'Название продавца',
                'address' => 'Адрес продавца',
                'ogrn' => 'ОГРН',
            ],
            'claim' => [
                'type' => 'Тип претензии',
                'status' => 'Статус претензии',
                'created_at' => 'Дата создания претензии',
                'was_in_repair' => 'Был ли товар в ремонте',
                'service_center_documents' => 'Документы сервисного центра',
                'previous_defect' => 'Предыдущий недостаток',
                'current_defect' => 'Текущий недостаток',
                'expertiseConducted' => 'Проводилась ли экспертиза',
                'expertiseData' => 'Данные экспертизы',
                'expertiseDefect' => 'Недостаток по экспертизе',
                'actualDefect' => 'Фактический недостаток',
            ],
        ];
    }
}
