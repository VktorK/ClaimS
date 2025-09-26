# BaseModel - Базовая модель с UUID

## Описание
`BaseModel` - это абстрактная базовая модель, которая обеспечивает использование UUID вместо автоинкрементных ID во всех моделях проекта.

## Особенности

### UUID вместо автоинкрементного ID
- Все модели, наследующиеся от `BaseModel`, автоматически используют UUID в качестве первичного ключа
- UUID генерируется автоматически при создании записи
- Поддерживается Laravel трейт `HasUuids`

### Настройки первичного ключа
```php
protected $keyType = 'string';     // Тип ключа - строка
public $incrementing = false;       // Отключен автоинкремент
```

## Использование

### Создание новой модели
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MyModel extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    // Связи и методы модели...
}
```

### Наследование от BaseModel
Все модели должны наследоваться от `BaseModel` вместо стандартного `Model`:

```php
// ✅ Правильно
class Product extends BaseModel
{
    // ...
}

// ❌ Неправильно
class Product extends Model
{
    // ...
}
```

## Исключения

### Модель User
Модель `User` наследуется от `Authenticatable`, поэтому не может наследоваться от `BaseModel`. В этом случае нужно добавить настройки UUID напрямую:

```php
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    
    // ...
}
```

## Миграции

### Создание таблицы с UUID
```php
Schema::create('table_name', function (Blueprint $table) {
    $table->uuid('id')->primary();  // UUID первичный ключ
    $table->string('name');
    $table->timestamps();
});
```

### Внешние ключи с UUID
```php
Schema::create('related_table', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->uuid('parent_id');
    $table->foreign('parent_id')->references('id')->on('parent_table')->onDelete('cascade');
    $table->timestamps();
});
```

## Преимущества UUID

1. **Глобальная уникальность** - UUID уникальны во всей системе
2. **Безопасность** - сложно угадать следующий ID
3. **Масштабируемость** - можно объединять данные из разных баз
4. **Отсутствие конфликтов** - при репликации данных

## Примеры использования

### Создание записи
```php
$product = Product::create([
    'title' => 'Новый товар',
    'price' => 1000.00
]);

echo $product->id; // 0199853c-0a17-72e3-9b91-61c7662b9a6d
```

### Поиск по UUID
```php
$product = Product::find('0199853c-0a17-72e3-9b91-61c7662b9a6d');
```

### Связи с UUID
```php
// В модели Product
public function seller(): BelongsTo
{
    return $this->belongsTo(User::class, 'seller_id');
}

// Использование
$product = Product::with('seller')->first();
echo $product->seller->name;
```

## Модели, использующие BaseModel

- ✅ `Product` - товары
- ✅ `Profile` - профили пользователей
- ⚠️ `User` - пользователи (наследуется от Authenticatable, настройки UUID добавлены напрямую)

## Рекомендации

1. Все новые модели должны наследоваться от `BaseModel`
2. При создании миграций всегда используйте `$table->uuid('id')->primary()`
3. Для внешних ключей используйте `$table->uuid('foreign_key')`
4. Не забывайте добавлять индексы для UUID полей при необходимости
