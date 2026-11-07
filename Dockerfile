FROM php:8.4-apache

# 1. Install ekstensi sistem & library PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Aktifkan mod_rewrite Apache untuk routing Laravel
RUN a2enmod rewrite

# 3. Arahkan Document Root ke subfolder public/ (Wajib demi keamanan!)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 4. Install Composer resmi
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Salin seluruh kode proyek ke container
WORKDIR /var/www/html
COPY . .

# 6. Install dependensi composer untuk mode produksi (tambahkan --ignore-platform-reqs agar selalu kompatibel)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# 7. Berikan hak akses ke folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Railway mengatur port aplikasi melalui environment variable $PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80

# 9. Jalankan migrasi otomatis jika DB terhubung, lalu jalankan Apache
CMD ["sh", "-c", "php artisan config:clear && php artisan route:clear && php artisan view:clear && (php artisan migrate --force || true) && apache2-foreground"]
