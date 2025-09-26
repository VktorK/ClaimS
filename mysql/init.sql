-- Создание базы данных если не существует
CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Предоставление прав пользователю laravel
GRANT ALL PRIVILEGES ON laravel.* TO 'laravel'@'%';

FLUSH PRIVILEGES;