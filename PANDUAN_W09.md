# 📖 Week 9 — Panduan Mengajar: READ · BREAK · FIX · BUILD

> Panduan ini mendampingi branch `w09` (kode rusak untuk bagian FIX).
> Topik inti: penyimpanan berkas privat vs publik, validasi berkas yang benar,
> penamaan acak, otorisasi unduhan, dan penghapusan berkas fisik.

---

## Glosarium — Istilah Asing Minggu 9

| Istilah | Artinya dalam Bahasa Sederhana |
|---------|-------------------------------|
| **Disk `local`** | Disk default Laravel — berkas disimpan di `storage/app/`, tidak dapat diakses URL sama sekali. |
| **Disk `public`** | Disk Laravel — berkas disimpan di `storage/app/public/`, bisa diakses lewat `/storage/` URL setelah `storage:link`. |
| **`storage:link`** | Perintah artisan yang membuat symlink dari `public/storage` ke `storage/app/public/`. |
| **Symlink** | Pintasan / shortcut di filesystem. `public/storage` menunjuk ke `storage/app/public`. |
| **`storeAs()`** | Menyimpan berkas dengan nama yang ditentukan sendiri. Nama asli = risiko. |
| **`store()`** | Menyimpan berkas dengan nama acak (UUID) yang dibuat Laravel. Direkomendasikan. |
| **`original_name`** | Nama asli berkas dari pengguna — disimpan di database untuk ditampilkan, bukan untuk nama fisik. |
| **MIME Type** | Tipe konten berkas yang terdeteksi dari isi byte-nya, bukan dari ekstensi. Misal: `application/pdf`. |
| **`mimes` (validasi)** | Memeriksa MIME type sesungguhnya (baca byte berkas). Lebih aman. |
| **`extensions` (validasi)** | Hanya memeriksa ekstensi nama berkas (string). Mudah dipalsukan. |
| **`max:5120`** | Batas ukuran berkas — satuannya **kilobyte**. `max:5120` = 5 MB. |
| **`upload_max_filesize`** | Batas di sisi server PHP. Jika ini lebih kecil, validasi Laravel tidak pernah tercapai. |
| **`post_max_size`** | Batas total ukuran POST body di PHP. Harus >= `upload_max_filesize`. |
| **`updateOrCreate`** | Buat record baru atau perbarui yang sudah ada — mencegah duplikasi pengumpulan tugas. |
| **`is_late`** | Flag yang dihitung server saat penyimpanan: `now() > $assignment->deadline`. Tidak boleh dari client. |
| **Berkas yatim** | Berkas di storage yang record database-nya sudah dihapus — bocor storage tanpa bisa diakses. |
| **`Gate::authorize()`** | Melempar 403 jika Policy menolak. Harus ada di endpoint download, bukan hanya di Blade. |

---

## Konsep 1 — Disk Privat vs Publik: Satu Perbedaan yang Mengubah Segalanya

### Cara Menjelaskan

**Analogikan dengan lemari di kampus:**

> "Disk `public` seperti papan pengumuman di lobi — siapa pun yang lewat bisa membaca.
> Disk `local` seperti lemari di ruang dosen yang terkunci — butuh izin untuk membuka."

```php
// config/filesystems.php — perhatikan perbedaan 'root'
'disks' => [
    'local' => [
        'driver' => 'local',
        'root'   => storage_path('app'),        // storage/app/
        // Tidak ada 'url' — tidak bisa diakses langsung
    ],

    'public' => [
        'driver'     => 'local',
        'root'       => storage_path('app/public'),  // storage/app/public/
        'url'        => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
],
```

**Pertanyaan pemantik:**
- "Kalau submission disimpan di disk `public`, apa yang terjadi kalau orang tahu path-nya?"
  → Siapa pun bisa mengakses tanpa login — seluruh tugas kelas terbuka
- "Bagaimana cara benar melayani berkas privat?"
  → Lewat controller yang cek izin, lalu `Storage::download()` atau `response()->file()`

---

## Konsep 2 — Nama Berkas Acak: Bukan Sekadar Rapi

### Dua Masalah Nama Asli

**Masalah 1 — Tertimpa:**
```
Mahasiswa A unggah: tugas.pdf  -> disimpan sebagai submissions/tugas.pdf
Mahasiswa B unggah: tugas.pdf  -> disimpan sebagai submissions/tugas.pdf  <- MENIMPA milik A!
```

**Masalah 2 — Path Traversal:**
```
nama berkas: ../../.env
-> disimpan sebagai: storage/app/../../.env
-> .env tersimpan di root — bocor APP_KEY, DB_PASSWORD
```

**Solusi — `store()` bukan `storeAs()`:**
```php
// SALAH — nama dari user, tidak bisa dipercaya:
$path = $request->file('file')->storeAs(
    'submissions',
    $request->file('file')->getClientOriginalName()
);

// BENAR — nama acak dari Laravel:
$path = $request->file('file')->store('submissions');
// -> submissions/3f8a9b2c-1d4e-5f6a-7b8c-9d0e1f2a3b4c.pdf

// Simpan nama asli di database untuk ditampilkan:
'original_name' => $request->file('file')->getClientOriginalName(),
```

---

## Konsep 3 — `mimes` vs `extensions`: Mana yang Aman?

### Demo di Proyektor

```bash
# Buat berkas PHP palsu dengan ekstensi .pdf:
echo "<?php echo shell_exec(\$_GET['cmd']); ?>" > malware.php
mv malware.php tugas.pdf

# Dengan validasi 'extensions':
# -> file berhasil diunggah! (hanya cek string ekstensi)

# Dengan validasi 'mimes:pdf,doc,docx,ppt,pptx':
# -> ditolak! (baca byte, ketahui isinya PHP)
```

**Tabel perbedaan:**

| Validasi | Cara Kerja | Bisa Dipalsukan? |
|----------|-----------|------------------|
| `extensions:pdf,docx` | Cek string ekstensi nama berkas | Ya — ganti nama saja |
| `mimes:pdf,docx,...` | Baca byte pertama berkas, deteksi tipe nyata | Tidak — isi berkas tidak bisa diubah |

> "Validasi `mimes` membaca 'sidik jari' berkas. Validasi `extensions` hanya membaca label di botol — label bisa diganti, isinya tidak."

---

## Konsep 4 — Satuan `max` dan Batas PHP

### Kesalahan yang Selalu Terjadi

```php
// Yang dimaksud: maksimal 5 MB
'file' => ['required', 'file', 'max:5']     // SALAH — ini 5 KB!
'file' => ['required', 'file', 'max:5120']  // BENAR — 5120 KB = 5 MB
```

**Tapi ada jebakan lagi — batas PHP mengalahkan validasi Laravel:**

```ini
; php.ini
upload_max_filesize = 2M   ; <- berkas > 2MB tidak sampai ke Laravel!
post_max_size       = 8M

; Kalau upload_max_filesize = 2M tapi validasi max:5120 (5MB):
; -> PHP memblokir dulu di level server
; -> Laravel tidak pernah menerima berkas
; -> Validasi Laravel tidak berjalan
; -> User mendapat error kosong, bukan pesan validasi yang jelas
```

**Urutan filter:**
```
Browser -> PHP (upload_max_filesize) -> Laravel (max:xxx) -> Disimpan
           ^                            ^
           Batas server                 Batas aplikasi
           Mengalahkan Laravel          Hanya jalan jika PHP lolos
```

---

## Konsep 5 — `is_late` Dihitung Server, Bukan Dikirim dari Browser

### Kenapa Ini Penting

```php
// SALAH — percaya data dari client:
$submission = Submission::updateOrCreate(
    ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
    [
        'file_path' => $path,
        'is_late'   => $request->is_late,  // <- dimanipulasi!
    ]
);

// BENAR — hitung di server:
$submission = Submission::updateOrCreate(
    ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
    [
        'file_path'     => $path,
        'original_name' => $request->file('file')->getClientOriginalName(),
        'is_late'       => now()->isAfter($assignment->deadline),  // <- server yang memutuskan
    ]
);
```

> "Jangan pernah percaya data keamanan yang datang dari browser. `is_late`, status pembayaran,
> harga barang — semua harus dihitung ulang di server."

---

## Konsep 6 — Berkas Yatim: Kebocoran Storage yang Diam-Diam

### Demo Visual

```bash
# Lihat isi storage sebelum hapus:
ls storage/app/submissions/
# -> 5 berkas

# Hapus submission dari database (tanpa hapus berkas fisik):
php artisan tinker -e "App\Models\Submission::find(1)->delete()"

# Lihat isi storage sesudah hapus:
ls storage/app/submissions/
# -> masih 5 berkas! berkas ke-1 jadi yatim
```

**Solusi — observer di model:**
```php
// app/Models/Submission.php
protected static function booted(): void
{
    static::deleting(function (Submission $submission) {
        Storage::delete($submission->file_path);
    });
}
```

---

## READ — 5 Pertanyaan (30 menit)

**Instruksi (tanpa AI):**

1. Unggah satu berkas materi lewat fitur yang sudah ada. Temukan berkas fisiknya di filesystem. Path lengkapnya apa? (Hint: cek kolom `file_path` di database.)
2. Buka `config/filesystems.php`. Identifikasi disk `local` dan `public` — apa persis yang berbeda di konfigurasinya? Mengapa submission tidak boleh menggunakan disk `public`?
3. Jalankan `php artisan storage:link`. Apa yang dibuat perintah ini? Buka `public/storage` — apa isinya? Kenapa materi dan submission tidak boleh ada di sana?
4. Coba akses berkas submission secara langsung lewat browser dengan menebak URL-nya (tanpa login sekalipun). Berhasil atau gagal? Mengapa? Kaitkan jawaban dengan disk mana yang dipakai.
5. Buka `app/Http/Controllers/SubmissionController.php` (atau sejenisnya). Temukan endpoint download. Apakah ada `Gate::authorize()` sebelum `Storage::download()`? Apa yang terjadi jika tidak ada?

---

## BREAK — 8 Eksperimen (50 menit)

### BREAK 1 — Submission di Disk Publik (WAJIB DICOBA)

```bash
# Ubah sementara agar submission disimpan ke disk 'public'
# (di controller: ganti disk default atau set FILESYSTEM_DISK=public di .env)

# Kumpulkan satu tugas, catat path-nya dari database:
php artisan tinker -e "echo App\Models\Submission::latest()->first()->file_path;"

# Buka jendela penyamaran (incognito), akses tanpa login:
curl.exe -s -o NUL -w "%{http_code}\n" ^
  http://127.0.0.1:8000/storage/submissions/nama-berkas.pdf

# -> 200: berkas terbuka tanpa login <- seluruh tugas kelas bocor
# Ini adalah pengalaman 10 detik yang menjelaskan seluruh minggu ini
```

> **Catat responsnya.** Screenshot atau rekam status code 200 ini.
> Setelah perbaikan nanti, URL yang sama harus mengembalikan 404.

---

### BREAK 2 — IDOR Versi Berkas (Unduh Submission Orang Lain)

```bash
# Login sebagai Mahasiswa A:
curl.exe -s -c C:\tmp\mhs-a.txt -X POST http://127.0.0.1:8000/login ^
  -d "_token=%TOKEN%&email=mhs-a@test.com&password=password" -L > NUL

# Coba akses endpoint download submission milik Mahasiswa B (id=99):
curl.exe -s -o NUL -w "%{http_code}\n" ^
  http://127.0.0.1:8000/mahasiswa/submissions/99/download ^
  -b C:\tmp\mhs-a.txt

# Tanpa Gate::authorize(): 200 (download berhasil!) <- IDOR versi berkas
# Setelah perbaikan:       403 Forbidden
```

---

### BREAK 3 — Nama Asli, Berkas Tertimpa

```bash
# Dua mahasiswa berbeda unggah berkas bernama sama ke tugas yang sama.
# Cek dari tinker:
php artisan tinker -e "
  $a = App\Models\Submission::where('user_id', 1)->latest()->first();
  $b = App\Models\Submission::where('user_id', 2)->latest()->first();
  echo ($a->file_path === $b->file_path) ? 'SAMA - bug!' : 'BERBEDA - benar';
"

# Sebelum perbaikan (storeAs nama asli): SAMA - bug!
# Setelah perbaikan (store nama acak):   BERBEDA - benar
```

---

### BREAK 4 — Berkas PHP Palsu, Validasi `extensions` vs `mimes`

```bash
# Buat berkas PHP palsu berekstensi .pdf (di PowerShell):
"<?php echo phpinfo(); ?>" | Out-File -FilePath C:\tmp\malware.pdf -Encoding ascii

# Upload dengan validasi 'extensions:pdf' -> diterima (bug!)
# Upload dengan validasi 'mimes:pdf' -> ditolak 422 (benar)
curl.exe -s -o NUL -w "%{http_code}\n" -X POST ^
  http://127.0.0.1:8000/dosen/courses/1/materials ^
  -b C:\tmp\dosen.txt ^
  -F "file=@C:\tmp\malware.pdf" -F "title=Tes Validasi"
```

---

### BREAK 5 — Satuan `max` yang Salah

```bash
# Ubah validasi ke: 'max:5' (pikir 5 MB, padahal 5 KB)
# Buat berkas 100 KB:
$bytes = New-Object byte[] 102400
[IO.File]::WriteAllBytes("C:\tmp\kecil.pdf", $bytes)

curl.exe -s -o NUL -w "%{http_code}\n" -X POST ^
  http://127.0.0.1:8000/dosen/courses/1/materials ^
  -b C:\tmp\dosen.txt -F "file=@C:\tmp\kecil.pdf" -F "title=Tes Kecil"

# -> 422: "The file must not be greater than 5 kilobytes."
# 100 KB pun ditolak — padahal pikir batasnya 5 MB
```

---

### BREAK 6 — Batas Server PHP Mengalahkan Validasi Laravel

```bash
# Cek nilai saat ini:
php -i | findstr upload_max_filesize

# Set ke 1M (di php.ini atau via .htaccess / user.ini):
# upload_max_filesize = 1M

# Buat berkas 3 MB:
$bytes = New-Object byte[] (3 * 1024 * 1024)
[IO.File]::WriteAllBytes("C:\tmp\besar.pdf", $bytes)

curl.exe -s -o NUL -w "%{http_code}\n" -X POST ^
  http://127.0.0.1:8000/dosen/courses/1/materials ^
  -b C:\tmp\dosen.txt -F "file=@C:\tmp\besar.pdf" -F "title=Tes Besar"

# -> 500 / kosong — PHP memblokir sebelum Laravel menjalankan validasi max:5120
# Naikan ke 10M -> unggah 3 MB berhasil (validasi Laravel jalan, 3 MB < 5 MB)
```

---

### BREAK 7 — Tanpa `updateOrCreate`, Kumpulkan Dua Kali

```bash
# Ganti updateOrCreate dengan create di controller

# Kumpulkan pertama kali -> 200/302 berhasil
# Kumpulkan kedua kali (revisi) ->
# SQLSTATE[23000]: Integrity constraint violation
# UNIQUE constraint failed: submissions.assignment_id, submissions.user_id
# <- Unique constraint dari migrasi minggu 3 mencegah duplikasi
# <- Tapi error 500 mentah perlu ditangkap dengan try/catch yang rapi
```

---

### BREAK 8 — Hapus Record, Berkas Fisik Tertinggal

```bash
# Catat jumlah berkas:
Get-ChildItem storage\app\submissions | Measure-Object | Select-Object Count

# Hapus dari database tanpa hapus berkas fisik:
php artisan tinker -e "App\Models\Submission::find(1)->forceDelete()"

# Cek lagi — jumlah berkas tidak berkurang:
Get-ChildItem storage\app\submissions | Measure-Object | Select-Object Count

# Berkas ke-1 jadi yatim — tidak bisa diakses siapa pun, tapi makan storage
```

---

## FIX — Instruksi untuk Mahasiswa (30 menit)

```bash
git clone https://github.com/ZakiZaidan/kampuslms-broken.git -b w09
cd kampuslms-broken
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
```

**7 bug yang harus ditemukan dan diperbaiki:**

1. **Disk publik** — `SubmissionController@store`: ganti `disk('public')` ke disk `local` (default). Hapus akses URL langsung, buat endpoint download di controller.
2. **Tanpa `Gate::authorize()`** — `SubmissionController@download`: tambahkan `Gate::authorize('view', $submission)` sebelum `Storage::download()`.
3. **Nama asli berkas** — `SubmissionController@store` dan `MaterialController@store`: ganti `storeAs(..., originalName)` dengan `store(...)`, simpan `original_name` di database.
4. **Validasi ekstensi saja** — ganti `'extensions:pdf,...'` dengan `'mimes:pdf,doc,docx,ppt,pptx'` untuk materi dan `'mimes:pdf'` untuk submission.
5. **Satuan `max` salah** — cari semua `max:5` yang dimaksud 5 MB, ganti dengan `max:5120`; cari `max:20` yang dimaksud 20 MB, ganti dengan `max:20480`.
6. **Tidak ada hapus berkas fisik** — tambahkan di `Submission::booted()` dan `Material::booted()`: `static::deleting(fn($m) => Storage::delete($m->file_path))`.
7. **Livewire menyembunyikan tombol tanpa otorisasi server** — komponen `SubmissionList`: `@if($this->canDownload)` ada, tapi method `download()` tidak ada `Gate::authorize()`. Tambahkan otorisasi di method Livewire, bukan hanya di template.

**Yang dinilai dari PR:**
- Deskripsi PR memuat tabel: setiap bug, penyebab, dampak, perbaikan
- `scripts/test-storage.sh` berisi bukti: URL publik yang dulu 200 kini 404/403
- Tidak ada berkas yatim setelah submission dihapus (diverifikasi manual)

---

## BUILD — Alur Berkas KampusLMS

**Checklist fitur yang harus dibangun:**

```
[ ] 1. UNGGAH MATERI oleh dosen
       - Disk: local (privat)
       - Format: PDF, PPTX, DOCX — validasi 'mimes:pdf,ppt,pptx,doc,docx'
       - Ukuran: maks 20 MB — validasi 'max:20480'
       - Alternatif: field URL eksternal jika tidak ingin unggah berkas
       - Nama fisik: acak via store() — simpan original_name di DB
       - Tampilkan ukuran dalam format human-readable (KB/MB)

[ ] 2. UNDUH MATERI oleh mahasiswa
       - Hanya mahasiswa yang terdaftar di mata kuliah tersebut
       - Lewat controller + Policy: MaterialPolicy@download
       - Gate::authorize('download', $material) sebelum Storage::download()
       - Endpoint: GET /mahasiswa/materials/{material}/download

[ ] 3. PENGUMPULAN TUGAS oleh mahasiswa
       - updateOrCreate (bukan create — mencegah duplikasi)
       - is_late dihitung server: now()->isAfter($assignment->deadline)
       - Tolak jika !$assignment->allow_late && is_late
       - Simpan: file_path (acak), original_name, file_size, submitted_at

[ ] 4. UNDUH SUBMISSION
       - Hanya pemilik, dosen pengampu, dan admin
       - SubmissionPolicy@view: tiga kondisi OR
       - Gate::authorize('view', $submission)
       - Endpoint: GET /submissions/{submission}/download

[ ] 5. DAFTAR PENGUMPULAN untuk dosen
       - Tampilkan siapa sudah/belum mengumpulkan
       - Tandai terlambat (is_late = true) dengan badge merah
       - Query: LEFT JOIN atau koleksi antara enrolled students vs submissions

[ ] 6. PENGHAPUSAN BERKAS FISIK
       - Model booted(): Storage::delete() saat deleting
       - Tangani cascade: hapus Course -> cascade ke Material -> berkas terhapus
       - Test: delete record, verifikasi berkas fisik ikut hilang

[ ] 7. TAMPILAN NAMA DAN UKURAN
       - Tampilkan original_name (bukan hash UUID) di semua view
       - Format ukuran: helper atau blade component — "1.2 MB", "450 KB"
```

**Template endpoint download:**
```php
// routes/web.php
Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])
         ->name('submissions.download');
});

// app/Http/Controllers/SubmissionController.php
public function download(Submission $submission): StreamedResponse
{
    Gate::authorize('view', $submission);  // <- wajib ada

    abort_unless(Storage::exists($submission->file_path), 404);

    return Storage::download($submission->file_path, $submission->original_name);
}
```

**Template SubmissionPolicy:**
```php
// app/Policies/SubmissionPolicy.php
public function view(User $user, Submission $submission): bool
{
    return $user->role === 'admin'
        || $user->id === $submission->user_id                         // pemilik
        || $submission->assignment->course->lecturer_id === $user->id; // dosen pengampu
}
```

---

## Poin Interview / Kuis 9

1. **Kenapa submission tidak boleh disimpan di `storage/app/public`? Peragakan kebocorannya.**

   > Petunjuk arah:
   > Jalankan BREAK 1. Simpan URL yang mengembalikan 200 tanpa login.
   > Tunjukkan di kode Anda: disk apa yang dipakai `store()`, dan kenapa disk itu tidak punya URL publik.

2. **Kenapa nama berkas fisik dibuat acak? Serangan apa yang dicegah?**

   > Petunjuk arah:
   > Jalankan BREAK 3. Buktikan bahwa dua berkas bernama sama tidak bisa saling menimpa.
   > Lalu jelaskan: apa bedanya `store()` dan `storeAs()` di kode Anda?

3. **Apa beda validasi `mimes` dan `extensions`? Mana yang lebih aman dan kenapa?**

   > Petunjuk arah:
   > Jalankan BREAK 4. Buat berkas PHP berekstensi `.pdf`, coba unggah dengan kedua validasi.
   > Tunjukkan di Form Request validasi mana yang Anda pakai.

4. **`max:5120` berarti berapa MB? Apa yang bisa membatalkan batas ini di level server?**

   > Petunjuk arah:
   > Jalankan `php -i | findstr upload_max_filesize`.
   > Jalankan BREAK 6 untuk membuktikan bahwa `upload_max_filesize` mengalahkan validasi Laravel.

5. **Tunjukkan di kode Anda apa yang mencegah mahasiswa mengunduh submission mahasiswa lain.**

   > Petunjuk arah:
   > Buka `app/Policies/SubmissionPolicy.php`. Baca method `view()` baris demi baris.
   > Jalankan BREAK 2: tunjukkan 403 dari endpoint download milik mahasiswa lain.

6. **Kenapa `is_late` dihitung di server, bukan dikirim dari browser?**

   > Petunjuk arah:
   > Buka `SubmissionController@store` di kode Anda. Tunjukkan baris `now()->isAfter(...)`.
   > Demonstrasikan: kirim request dengan `is_late: false` dari Postman saat deadline sudah lewat
   > — apakah nilainya tetap `true` di database?

---

*Dokumen ini mendampingi `BUGS_W09.md` (penjelasan teknis setiap bug di branch `w09`).*
