# Используем официальный образ PHP 8.3 с FPM
FROM php:8.3.12-fpm

# Устанавливаем необходимые расширения PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Рабочая директория
WORKDIR /var/www/html/parser

# Копируем исходный код
#COPY . /var/www/html/parser

# Устанавливаем зависимости
#RUN composer install --no-dev --optimize-autoloader

# Устанавливаем права
#RUN chown -R www-data:www-data /var/www/html/parser

# Порт, который будет слушать PHP-FPM
EXPOSE 9000

# Команда для запуска PHP-FPM
#CMD ["php-fpm"]

CMD sh -c "composer install --no-dev --optimize-autoloader && php-fpm"
