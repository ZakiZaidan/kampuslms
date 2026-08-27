# Panduan Instalasi Composer & Upgrade PHP

Dokumen ini berisi panduan langkah demi langkah untuk melakukan instalasi Composer, cara melakukan upgrade versi PHP di Windows, serta rekomendasi tempat upload gambar untuk keperluan dokumentasi.

---

## 1. Upgrade Versi PHP (di Windows)

Berdasarkan log yang ada sebelumnya, Anda sepertinya menggunakan **Laragon**. Berikut adalah cara termudah untuk meng-upgrade versi PHP di Laragon:

1. **Download PHP:**
   Kunjungi situs resmi PHP untuk Windows:
   👉 **[https://windows.php.net/download/](https://windows.php.net/download/)**
   *(Pilih versi terbaru, misalnya PHP 8.4 atau 8.5. Pastikan mendownload file `.zip` versi **Thread Safe (TS)** dengan arsitektur x64).*
2. **Ekstrak File:**
   Ekstrak file `.zip` yang sudah didownload. Ubah nama foldernya agar rapi (misalnya `php-8.4.0`).
3. **Pindahkan ke Laragon:**
   Pindahkan folder hasil ekstrak tersebut ke dalam folder PHP di Laragon, yang secara default berada di:
   `C:\laragon\bin\php\`
4. **Ubah Versi di Laragon:**
   - Buka aplikasi Laragon.
   - Klik **Kanan** sembarang tempat pada layar Laragon > pilih **PHP** > **Version** > Klik versi PHP yang baru saja Anda masukkan.
5. **Restart:**
   Klik **Stop** lalu **Start All** kembali pada Laragon agar perubahan versi berlaku.

*(Catatan: Jika sewaktu-waktu Anda menggunakan **XAMPP**, cara termudahnya adalah dengan mendownload dan menginstall XAMPP versi terbaru dari [apachefriends.org](https://www.apachefriends.org/)).*

---

## 2. Instalasi / Update Composer

Composer adalah manager paket (*dependency manager*) untuk PHP yang sangat dibutuhkan untuk menjalankan Laravel.

1. **Download Composer:**
   Unduh installer otomatis Composer untuk Windows melalui link berikut:
   👉 **[https://getcomposer.org/Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)** (Link Unduhan Langsung)
   Atau kunjungi halaman utamanya di: [https://getcomposer.org/download/](https://getcomposer.org/download/)
2. **Jalankan Installer:**
   Buka file `Composer-Setup.exe` yang sudah diunduh.
3. **Pilih PHP Path:**
   Saat instalasi, Anda akan diminta untuk memilih lokasi file `php.exe` (*Choose the command-line PHP*). Arahkan *browse* ke lokasi PHP versi terbaru Anda (misalnya `C:\laragon\bin\php\php-8.4.0\php.exe`).
4. **Selesaikan Instalasi:**
   Klik *Next* hingga selesai (*Finish*).
5. **Cek Instalasi:**
   Buka terminal/Command Prompt **baru**, lalu ketik:
   ```bash
   composer -v
   ```
   Jika muncul logo teks Composer, berarti instalasi sukses.

---

## 3. Langkah Menjalankan Project



1. **Install Dependencies:**
   ```bash
   composer install
   ```

2. **Buat file `.env` & Generate Key:**
   - Copy file `.env.example` dan ubah namanya menjadi `.env` (atau jalankan perintah `copy .env.example .env`)
   - Jalankan perintah ini untuk membuat Application Key:
   ```bash
   php artisan key:generate
   ```

3. **Setting Database (MySQL):**
   Buka file `.env`, cari pengaturan database, lalu sesuaikan isinya menjadi seperti ini:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kampus_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Pastikan mahasiswa sudah membuat database kosong bernama `kampus_db` di phpMyAdmin / HeidiSQL, dan MySQL di Laragon/XAMPP sudah dalam keadaan berjalan).*

   **CARA MEMBUAT DATABASE DI PHPMYADMIN**

   1. Pastikan Laragon sudah berjalan (Tombol "Start All" berwarna hijau).
   2. Buka browser dan akses phpMyAdmin (Biasanya [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)).
   3. Klik tab **Database** (Basis Data) di kiri atas.
   4. Pada kolom **Buat database**, ketik nama database yang diinginkan (misal: `kampus_db`).
   5. Pastikan "Charset" diset ke **utf8mb4** dan "Collation" ke **utf8mb4_unicode_ci** (agar support emoji).
   6. Klik tombol **Buat** (Create).
   

4. **Jalankan Migrasi Database:**
   ```bash
   php artisan migrate
   ```

5. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   Aplikasi sudah siap diakses melalui browser di alamat `http://localhost:8000`.


---

## Setelah setup selesai langsung kerjakan bagian 1.3 dan 1.4 di github pak aidil
https://github.com/aidilsaputrakirsan/Pengajaran-MataKuliah/blob/main/Ganjil-2026-2027/Pemrograman-Web/mahasiswa/02-modul-minggu-01-03.md

## 4. Panduan Mengelola Repositori GitHub

Setelah project berjalan, Anda harus mengunggah (push) hasil kerja Anda ke GitHub dan menambahkan Dosen serta Asisten Dosen sebagai kolaborator.

### A. Cara Membuat Repositori di GitHub
1. Buka browser dan login ke akun [GitHub](https://github.com/).
2. Di pojok kanan atas, klik tanda **+** lalu pilih **New repository**.
3. Isi kolom **Repository name** Nama: kampuslms-kelompok-XX
4. Biarkan opsi pada **Public** (atau **Private** jika memang diinstruksikan demikian).
5. **JANGAN** mencentang *Add a README file*, *Add .gitignore*, atau *Choose a license*. Biarkan bagian tersebut kosong.
6. Klik tombol hijau **Create repository**.

### B. Cara Push Code ke GitHub (Pertama Kali)
Setelah repositori berhasil dibuat, GitHub akan memberikan link untuk repositori Anda (contoh: `https://github.com/username/tugas-pemweb-kampus.git`). Buka terminal Anda (pastikan posisi berada di dalam folder project Laravel) dan jalankan perintah ini secara berurutan:

```bash
git init
git add .
git commit -m "commit pertama: setup laravel"
git branch -M main
git remote add origin https://github.com/username/nama-repo-anda.git
git push -u origin main
```
*(Catatan: Jangan lupa ganti link `https://github.com/...` di atas dengan link repositori Anda sendiri yang didapatkan pada Langkah A).*

### C. Cara Menambahkan Dosen dan Asdos (Collaborator)
Agar Dosen dan Asisten Dosen bisa memantau dan menilai tugas Anda, pastikan untuk menambahkan mereka sebagai kolaborator:
1. Buka halaman repositori Anda di GitHub.
2. Klik tab **Settings** (biasanya di sebelah kanan atas menu repositori).
3. Di menu sebelah kiri, pilih **Collaborators**. (Jika diminta, masukkan ulang password akun GitHub Anda).
4. Klik tombol hijau **Add people**.
5. Masukkan **Username GitHub** atau **Email** dari Dosen dan Asisten Dosen Anda.
6. Klik nama mereka yang muncul, lalu pilih **Add to this repository**.
7. Hubungi Dosen/Asdos yang bersangkutan agar mereka menerima undangan (*invitation*) yang masuk ke email/akun GitHub mereka.

---

