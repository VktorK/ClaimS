<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class BaseModel extends Model
{
    use HasFactory, HasUuids;

    /**
     * Указываем, что первичный ключ - это UUID
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Получить имя первичного ключа
     */
    public function getKeyName()
    {
        return 'id';
    }

    /**
     * Получить тип первичного ключа
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Проверить, что модель использует автоинкрементный ключ
     */
    public function getIncrementing()
    {
        return false;
    }
}
