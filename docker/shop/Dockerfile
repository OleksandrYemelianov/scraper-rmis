FROM php:8.3.12-fpm

# Устанавливаем все необходимые системные пакеты сразу
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    libmemcached-dev \
    memcached \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем memcached расширение
RUN pecl install memcached && \
    docker-php-ext-enable memcached

# Настраиваем GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Устанавливаем PHP расширения (только те, которые точно доступны)
RUN docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    mysqli \
    gd

WORKDIR /var/www/html/shop

EXPOSE 9000

CMD ["php-fpm"]
