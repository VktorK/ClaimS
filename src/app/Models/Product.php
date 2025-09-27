<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'warranty_period',
        'seller_id',
        'claim_id',
        'user_id',
    ];

    /**
     * Поля, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'price' => 'decimal:2',
        'date_of_buying' => 'date',
    ];

    /**
     * Связь с пользователем (создатель товара)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Связь с продавцом
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    /**
     * Связь с претензией
     */
    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'product_id');
    }

    /**
     * Получить количество претензий
     */
    public function getClaimsCountAttribute(): int
    {
        return $this->claims()->count();
    }

    /**
     * Получить количество активных претензий
     */
    public function getActiveClaimsCountAttribute(): int
    {
        return $this->claims()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    /**
     * Получить отформатированную цену
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price ? number_format($this->price, 2) . ' ₽' : 'Не указана';
    }

    /**
     * Получить отформатированный срок гарантии
     */
    public function getFormattedWarrantyPeriodAttribute(): string
    {
        if (!$this->warranty_period) {
            return 'Не указан';
        }
        
        $months = $this->warranty_period;
        if ($months == 1) {
            return '1 месяц';
        } elseif ($months < 5) {
            return $months . ' месяца';
        } else {
            return $months . ' месяцев';
        }
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
