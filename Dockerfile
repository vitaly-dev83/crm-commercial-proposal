FROM php:8.2-apache

# Устанавливаем необходимые расширения PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Устанавливаем GD расширение для обработки изображений
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip

# Включаем mod_rewrite для Apache
RUN a2enmod rewrite

# Устанавливаем Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копируем файлы проекта
COPY . /var/www/html/

# Устанавливаем зависимости Composer
RUN composer install --no-dev --optimize-autoloader

# Устанавливаем права
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/proposals

# Настраиваем Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

EXPOSE 80