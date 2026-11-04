#!/bin/bash

# BUG 2 (w12): Tidak ada 'set -e' di awal skrip!
# Jika salah satu perintah di bawah ini gagal (misal composer error atau migrasi database gagal),
# bash akan mengabaikan error tersebut dan tetap melanjutkan eksekusi hingga akhir.
# Akibatnya website dibuka kembali (php artisan up) dalam kondisi rusak!
# Solusi: Tambahkan 'set -e' di baris paling awal.

echo "🚀 Memulai deployment KampusLMS..."

php artisan down --render="errors::503"

git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction

php artisan migrate --force

# BUG 6 (w12): php artisan db:seed dijalankan di setiap kali deploy!
# Seeder hanya boleh dijalankan SEKALI saat inisialisasi server baru.
# Menjalankannya di skrip deploy otomatis akan menghapus/menimpa data produksi,
# merusak transaksi yang sudah berjalan, atau memicu duplikasi data.
# Solusi: Hapus baris db:seed di bawah ini dari deploy.sh!
php artisan db:seed --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# BUG 2 (lanjutan): Tidak ada 'php artisan queue:restart'!
# Queue worker berjalan sebagai daemon di RAM. Tanpa perintah ini,
# worker akan TETAP menjalankan kode PHP lama di memori RAM dan tidak pernah
# mengeksekusi logika baru hasil git pull.
# Solusi: Tambahkan 'php artisan queue:restart' sebelum 'php artisan up'!

php artisan up

echo "✅ Deployment selesai!"
