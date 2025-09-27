<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seller extends BaseModel
{
    /**
     * Поля, которые можно массово присваивать
     */
    protected $fillable = [
        'title',
        'address',
        'ogrn',
        'user_id',
    ];

    /**
     * Связь с товарами
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Получить количество товаров продавца
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }

    /**
     * Получить общую стоимость товаров продавца
     */
    public function getTotalValueAttribute(): float
    {
        return $this->products()->sum('price') ?? 0;
    }

    /**
     * Проверить, есть ли ОГРН
     */
    public function hasOgrn(): bool
    {
        return !empty($this->ogrn);
    }

    /**
     * Получить краткое название (первые 50 символов)
     */
    public function getShortTitleAttribute(): string
    {
        return mb_strlen($this->title) > 50 
            ? mb_substr($this->title, 0, 50) . '...' 
            : $this->title;
    }
}
