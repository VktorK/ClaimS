<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends BaseModel
{
    /**
     * Поля, которые можно массово присваивать
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'type',
        'was_in_repair',
        'service_center_documents',
        'previous_defect',
        'current_defect',
        'expertiseConducted',
        'expertiseData',
        'expertiseDefect',
        'actualDefect',
        'claimed_amount',
        'claim_date',
        'resolution_date',
        'resolution_notes',
        'attachments',
    ];

    /**
     * Поля, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'was_in_repair' => 'boolean',
        'expertiseConducted' => 'boolean',
        'claimed_amount' => 'decimal:2',
        'claim_date' => 'date',
        'resolution_date' => 'date',
        'attachments' => 'array',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с товаром
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Получить статус претензии на русском языке
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Ожидает рассмотрения',
            'in_progress' => 'В работе',
            'resolved' => 'Решена',
            'rejected' => 'Отклонена',
            default => 'Неизвестно'
        };
    }

    /**
     * Получить тип претензии на русском языке
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'defect' => 'Брак',
            'warranty' => 'Гарантия',
            'return' => 'Возврат',
            'complaint' => 'Жалоба',
            default => 'Неизвестно'
        };
    }

    /**
     * Получить отформатированную сумму претензии
     */
    public function getFormattedClaimedAmountAttribute(): string
    {
        return $this->claimed_amount ? number_format($this->claimed_amount, 2) . ' ₽' : 'Не указана';
    }

    /**
     * Проверить, решена ли претензия
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Проверить, отклонена ли претензия
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Проверить, активна ли претензия (не решена и не отклонена)
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['pending', 'in_progress']);
    }
}
