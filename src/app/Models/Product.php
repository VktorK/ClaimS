<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends BaseModel
{

    /**
     * Поля, которые можно массово присваивать
     */
    protected $fillable = [
        'title',
        'model',
        'serial_number',
        'price',
        'date_of_buying',
        'seller_id',
        'claim_id',
    ];

    /**
     * Поля, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'price' => 'decimal:2',
        'date_of_buying' => 'date',
    ];

    /**
     * Связь с продавцом (пользователем)
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Связь с претензией
     * Примечание: Модель Claim будет создана позже
     */
    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class, 'claim_id');
    }

    /**
     * Получить отформатированную цену
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? number_format($this->price, 2) . ' ₽' : 'Не указана';
    }

    /**
     * Проверить, есть ли серийный номер
     */
    public function hasSerialNumber(): bool
    {
        return !empty($this->serial_number);
    }

    /**
     * Получить возраст товара в днях
     */
    public function getAgeInDaysAttribute(): ?int
    {
        if (!$this->date_of_buying) {
            return null;
        }
        
        return (int) $this->date_of_buying->diffInDays(now());
    }
}
