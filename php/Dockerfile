FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libicu-dev \
    default-mysql-client

# Очистка кеша
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка PHP расширений
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Создание пользователя для приложения
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Создание рабочей директории
RUN mkdir -p /var/www/html

# Установка прав доступа
RUN chown -R www:www /var/www/html

# Смена пользователя на www
USER www

# Рабочая директория
WORKDIR /var/www/html

# Открываем порт 9000
EXPOSE 9000

CMD ["php-fpm"]