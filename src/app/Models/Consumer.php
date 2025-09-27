<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Модель потребителя
 */
class Consumer extends BaseModel
{
    use SoftDeletes;

    /**
     * Поля, которые можно массово присваивать
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'address',
        'passport',
        'passport_issued_by',
        'passport_issued_date',
        'inn',
    ];

    /**
     * Поля, которые должны быть приведены к определенным типам
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'passport_issued_date' => 'date',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить полное имя потребителя
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->last_name . ' ' . $this->first_name;
        if ($this->middle_name) {
            $name .= ' ' . $this->middle_name;
        }
        return $name;
    }

    /**
     * Получить сокращенное имя потребителя
     */
    public function getShortNameAttribute(): string
    {
        $name = $this->last_name . ' ' . mb_substr($this->first_name, 0, 1) . '.';
        if ($this->middle_name) {
            $name .= ' ' . mb_substr($this->middle_name, 0, 1) . '.';
        }
        return $name;
    }

    /**
     * Получить отформатированный паспорт
     */
    public function getFormattedPassportAttribute(): string
    {
        if (strlen($this->passport) === 10) {
            return substr($this->passport, 0, 4) . ' ' . substr($this->passport, 4, 6);
        }
        return $this->passport;
    }

    /**
     * Получить отформатированный ИНН
     */
    public function getFormattedInnAttribute(): string
    {
        if ($this->inn && strlen($this->inn) === 12) {
            return substr($this->inn, 0, 2) . ' ' . substr($this->inn, 2, 2) . ' ' . 
                   substr($this->inn, 4, 2) . ' ' . substr($this->inn, 6, 3) . ' ' . 
                   substr($this->inn, 9, 3);
        }
        return $this->inn ?? '';
    }
}
