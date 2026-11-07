#!/bin/bash
set -e

echo "🚀 Memulai deployment KampusLMS..."

# 1. Masuk ke mode maintenance
php artisan down --render="errors::503" || true

# 2. Ambil update kode terbaru
git pull origin production

# 3. Install dependensi composer untuk mode produksi
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Jalankan migrasi database
php artisan migrate --force

# 5. Kompilasi dan optimasi cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Restart queue worker agar memuat kode baru
php artisan queue:restart || true

# 7. Buka kembali aplikasi dari mode maintenance
php artisan up

echo "✅ Deployment KampusLMS selesai!"
