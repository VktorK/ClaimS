<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends BaseModel
{

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'gender',
        'phone',
        'avatar',
        'country',
        'city',
        'address',
        'postal_code',
        'job_title',
        'company',
        'bio',
        'skills',
        'website',
        'linkedin',
        'twitter',
        'facebook',
        'instagram',
        'is_public',
        'show_email',
        'show_phone',
        'preferences',
    ];

    protected $appends = ['avatar_url', 'full_name'];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить полное имя
     */
    public function getFullNameAttribute(): string
    {
        $name = trim($this->first_name . ' ' . $this->last_name);
        return $name ?: $this->user->name;
    }

    /**
     * Получить возраст
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) {
            return null;
        }
        
        return $this->birth_date->age;
    }

    /**
     * Получить аватар или дефолтный
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && \Storage::disk('public')->exists($this->avatar)) {
            return asset('storage/' . $this->avatar);
        }
        
        // Дефолтный аватар на основе пола
        $defaultAvatar = 'default-avatar.png';
        if ($this->gender === 'female') {
            $defaultAvatar = 'default-female.png';
        } elseif ($this->gender === 'male') {
            $defaultAvatar = 'default-male.png';
        }
        
        return asset('images/' . $defaultAvatar);
    }

    /**
     * Проверить, заполнен ли профиль
     */
    public function isComplete(): bool
    {
        $requiredFields = ['first_name', 'last_name', 'phone'];
        
        foreach ($requiredFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        
        return true;
    }
}
