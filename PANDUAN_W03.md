# 📖 Week 3 — Panduan READ · BREAK · BUILD

> Panduan ini mendampingi branch `w03` (kode rusak untuk bagian FIX).
> Kerjakan bagian ini secara berurutan.

---

## 3.3.1 READ — Baca Skema Sebelum Menulis (30 menit)

Sebelum menyentuh kode, kerjakan bersama kelompok. Tulis jawaban di buku/kertas — jangan cek kode dulu.

---

### ✏️ Tugas 1 — Gambar Ulang ERD

Tanpa melihat dokumen, gambar ulang ERD KampusLMS di papan/kertas. ERD harus memuat:

```
users ──────────── courses (via course_user)
  │                    │
  │ (lecturer_id)       ├── assignments
  └──────────────────── │       │
                        │       └── submissions ── grades
                        └── materials
```

Kolom yang wajib muncul di setiap tabel:

| Tabel | Kolom kunci |
|-------|-------------|
| `users` | id, name, email, password, **role** |
| `courses` | id, code, name, sks, **lecturer_id**, status |
| `course_user` | course_id, user_id, enrolled_at |
| `assignments` | id, course_id, title, due_at |
| `submissions` | id, assignment_id, user_id, submitted_at |
| `grades` | id, **submission_id**, score, feedback |
| `materials` | id, course_id, title, order |

---

### ✏️ Tugas 2 — Tentukan `onDelete` untuk Setiap Foreign Key

Isi tabel ini sebelum melihat kode:

| Foreign Key | Dari Tabel | Ke Tabel | onDelete yang Tepat | Alasan |
|-------------|------------|----------|---------------------|--------|
| `lecturer_id` | `courses` | `users` | `restrict` | ← isi sendiri |
| `course_id` | `course_user` | `courses` | ? | ← isi sendiri |
| `user_id` | `course_user` | `users` | ? | ← isi sendiri |
| `course_id` | `assignments` | `courses` | ? | ← isi sendiri |
| `assignment_id` | `submissions` | `assignments` | ? | ← isi sendiri |
| `user_id` | `submissions` | `users` | ? | ← isi sendiri |
| `submission_id` | `grades` | `submissions` | ? | ← isi sendiri |
| `course_id` | `materials` | `courses` | ? | ← isi sendiri |

**Acuan pilihan:**
- `cascadeOnDelete` → anak ikut terhapus ketika induk dihapus
- `restrictOnDelete` → tolak penghapusan induk selama anak masih ada
- `nullOnDelete` → set kolom FK menjadi NULL

---

### ✏️ Tugas 3 — Diskusi Kelompok

**Pertanyaan 1:** Kalau seorang dosen dihapus dari sistem, apa yang seharusnya terjadi pada mata kuliahnya? Kenapa dirancang begitu?

> **Jawaban yang diharapkan:** Penghapusan dosen seharusnya **ditolak** (`restrict`)
> selama dosen masih mengampu mata kuliah. Alasannya: mata kuliah memiliki
> mahasiswa yang terdaftar, tugas, dan nilai — semua data itu tidak boleh
> hilang hanya karena admin salah tekan tombol hapus pada akun dosen.
> Kalau mau menghapus dosen, harus ada proses: alihkan MK ke dosen lain
> dulu, baru akun dosen bisa dihapus.

---

**Pertanyaan 2:** Kenapa `grades.submission_id` bersifat `unique`, bukan sekadar `index` biasa?

> **Jawaban yang diharapkan:** Karena relasi antara `grades` dan `submissions`
> adalah **1:1** — satu submission hanya boleh mendapat **tepat satu** nilai.
> `index` biasa hanya mempercepat pencarian, tidak mencegah duplikat.
> `unique` memastikan database menolak baris kedua dengan `submission_id`
> yang sama. Tanpanya, satu tugas bisa dinilai dua kali (misal: guru menilai
> ulang dan menekan tombol dua kali) dan sistem tidak punya cara membedakan
> nilai mana yang sahih.

---

## 3.3.2 BREAK — Lima Kerusakan (45 menit)

> ⚠️ **PENTING:** Kerjakan di branch lokal terpisah. Kembalikan setelah selesai.
> Jangan commit kode broken ke branch utama kelompok.

### Setup Cepat

```bash
git checkout -b eksperimen-break
php artisan migrate:fresh  # mulai dari database bersih
```

---

### 💥 Kerusakan 1 — Hapus Unique Composite `course_user`

**Yang dilakukan:**
Di migrasi `course_user`, hapus (atau comment) baris:
```php
$table->unique(['course_id', 'user_id']);
```
Jalankan `migrate:fresh`, lalu di Tinker:
```php
$user = App\Models\User::first();
$course = App\Models\Course::first();

$course->students()->attach($user->id);
$course->students()->attach($user->id);  // seharusnya error, tapi tidak

// Lihat hasilnya:
DB::table('course_user')->where('user_id', $user->id)->get();
// → 2 baris identik — mahasiswa terdaftar dua kali
```

**Yang Anda pelajari:**
Validasi di controller tidak cukup. Race condition (dua klik bersamaan)
atau dua request paralel bisa menghasilkan duplikat meski controller
"sudah mengecek". Database constraint adalah satu-satunya jaminan absolut.

---

### 💥 Kerusakan 2 — Mass Assignment Nyata dengan `$fillable` Longgar

**Yang dilakukan:**
1. Pastikan `User::$guarded = []` (sudah ada di w03)
2. Jalankan di Tinker:

```php
// Ini persis yang dilakukan controller saat menerima $request->all()
App\Models\User::create([
    'name'     => 'Penyusup',
    'email'    => 'penyusup@kampus.test',
    'password' => 'rahasia123',
    'role'     => 'admin',   // ← field ini TIDAK ADA di form pendaftaran
]);

// Verifikasi
App\Models\User::where('email', 'penyusup@kampus.test')->value('role');
// → 'admin'
```

**Yang Anda pelajari:**
CSRF token **tidak mencegah ini**. CSRF mencegah request dari situs lain.
Penyerang yang membuka form Anda di browser dan menambah field `role` di
DevTools punya CSRF token yang valid. Yang mencegah ini adalah `$fillable`
yang ketat — kolom `role` tidak boleh ada di sana.

**Cara membuktikan via HTTP sungguhan:**
```bash
# 1. Ambil halaman form, simpan cookie dan token CSRF
TOKEN=$(curl -s -c cookies.txt http://localhost:8000/users/create \
  | grep -o 'name="_token" value="[^"]*"' | cut -d'"' -f4)

# 2. Kirim request dengan field 'role' yang tidak ada di form
curl -X POST http://localhost:8000/users \
  -b cookies.txt \
  -d "_token=$TOKEN" \
  -d "name=Penyusup" \
  -d "email=penyusup2@kampus.test" \
  -d "password=rahasia123" \
  -d "role=admin"

# 3. Verifikasi di Tinker
# App\Models\User::where('email','penyusup2@kampus.test')->value('role');
```

---

### 💥 Kerusakan 3 — `$guarded = []` Penuh

**Yang dilakukan:**
Pastikan `User` model memiliki `$guarded = []` (sudah di w03), lalu ulangi
Kerusakan 2. Amati bahwa sekarang **SEMUA kolom** bisa diisi, termasuk:
- `remember_token` → bisa dipakai untuk session hijacking
- `email_verified_at` → bisa dimanipulasi agar akun terlihat sudah diverifikasi

```php
App\Models\User::create([
    'name'               => 'Admin Palsu',
    'email'              => 'admin@kampus.test',
    'password'           => 'rahasia',
    'role'               => 'admin',
    'email_verified_at'  => now(),  // seolah sudah verifikasi email
]);
```

**Yang Anda pelajari:**
`$guarded = []` adalah anti-pattern yang berbahaya. Selalu gunakan
`$fillable` dengan daftar kolom yang eksplisit. Kolom sensitif seperti
`role`, `is_verified`, `remember_token` **tidak boleh** ada di `$fillable`.

---

### 💥 Kerusakan 4 — `down()` Kosong

**Yang dilakukan:**
Pastikan `assignments` migration punya `down()` kosong (sudah di w03), lalu:
```bash
php artisan migrate      # berhasil, tabel assignments terbuat
php artisan migrate:rollback  # "berhasil" tapi tabel TIDAK terhapus
php artisan migrate      # ERROR: Table 'assignments' already exists
```

Atau langsung:
```bash
php artisan migrate:refresh
# → ERROR karena tabel assignments masih ada
```

**Yang Anda pelajari:**
`down()` yang kosong membuat migrasi tidak reversible. CI yang menjalankan
`migrate:refresh` akan **merah** — artinya tidak ada PR yang bisa di-merge.
Ini adalah bug yang sangat umum dan sangat mudah diperbaiki.

---

### 💥 Kerusakan 5 — `cascadeOnDelete` pada `lecturer_id`

**Yang dilakukan:**
Pastikan `courses.lecturer_id` memakai `cascadeOnDelete` (sudah di w03), lalu:
```php
// Migrasi sudah berjalan dengan cascade
$dosen = App\Models\User::where('role', 'lecturer')->first();

// Berapa MK yang diajar?
echo $dosen->taughtCourses()->count();  // misal: 2 MK

// Hapus dosen
$dosen->delete();

// Berapa MK yang tersisa?
App\Models\Course::count();  // berkurang 2 — MK ikut terhapus!

// Berapa submission mahasiswa yang hilang?
// (tergantung cascade chain: courses → assignments → submissions → grades)
```

**Yang Anda pelajari:**
`cascade` berarti "hapus anak jika induk dihapus". Untuk konten (materi MK),
ini masuk akal. Untuk **entitas penting** (dosen, mata kuliah dengan nilai
mahasiswa), ini bisa menyebabkan kehilangan data berantai yang tidak bisa
dikembalikan. Desain yang benar: gunakan `restrict` agar kesalahan manusia
tertangkap lebih dulu.

---

### 🔄 Kembalikan Semuanya

```bash
git checkout w01  # kembali ke branch bersih
git branch -D eksperimen-break  # hapus branch eksperimen
php artisan migrate:fresh  # reset database ke keadaan bersih
```

---

### 📝 Catat Temuan

Setiap anggota kelompok catat temuan di `docs/minggu-03-<nama>.md`:

```markdown
# Temuan Week 3 — [Nama]

## Kerusakan 1 — Unique Composite
- Yang terjadi: ...
- Dampak nyata yang saya lihat: ...

## Kerusakan 2 — Mass Assignment
- Role yang berhasil disuntikkan: ...
- Kesimpulan: CSRF vs $fillable = ...

[dst.]
```

---

## 3.3.3 BUILD — Milestone M1 (Tugas 1)

> Ini adalah checklist deliverable yang dinilai. Gunakan branch `main`/`master`
> kelompok, bukan branch `w03`.

### Checklist Migrasi

```
[ ] create_users_table      — sudah ada (default Laravel)
[ ] create_courses_table    — dengan lecturer_id + restrictOnDelete
[ ] create_course_user_table — dengan unique(['course_id','user_id'])
[ ] create_assignments_table — dengan down() yang benar
[ ] create_submissions_table — dengan unique(['assignment_id','user_id'])
[ ] create_grades_table      — SETELAH submissions, submission_id unique
[ ] create_materials_table   — dengan cascadeOnDelete pada course_id

Verifikasi:
  php artisan migrate:fresh  → hijau
  php artisan migrate:refresh → hijau
```

### Checklist Model

```
[ ] User     — $fillable ketat (tanpa role), relasi courses/taughtCourses/submissions
[ ] Course   — relasi lecturer/students/assignments/materials
[ ] Assignment — relasi course/submissions, cast due_at
[ ] Submission — relasi assignment/user/grade, cast submitted_at
[ ] Grade    — relasi submission, cast score
[ ] Material — relasi course

Verifikasi casts() sebagai METHOD (bukan properti $casts):
  protected function casts(): array { return [...]; }
```

### Checklist Factory + Seeder

```
[ ] UserFactory  — bisa generate user dengan role tertentu
[ ] CourseFactory — faker untuk code, name, sks
[ ] AssignmentFactory
[ ] SubmissionFactory — dengan submitted_at (sebagian > due_at = terlambat)
[ ] GradeFactory

[ ] DatabaseSeeder memanggil:
    - 1 admin (akun demo tetap)
    - 3 dosen (akun demo tetap)
    - 30 mahasiswa
    - 5 mata kuliah
    - ≥100 submission

[ ] Akun demo (email & password tetap, didokumentasikan di README):
    admin@kampus.test  / password
    dosen1@kampus.test / password
    mhs1@kampus.test   / password
```

### Checklist CRUD Mata Kuliah

```
[ ] Route: GET  /courses           → index
[ ] Route: GET  /courses/create    → create form
[ ] Route: POST /courses           → store
[ ] Route: GET  /courses/{id}      → show
[ ] Route: GET  /courses/{id}/edit → edit form
[ ] Route: PUT  /courses/{id}      → update
[ ] Route: DELETE /courses/{id}    → destroy

[ ] Controller tidak pakai $request->all()
[ ] View menampilkan nama dosen (relasi lecturer)
```

### Checklist CRUD Pengguna

```
[ ] Route resource: Route::resource('users', UserController::class)
[ ] store() memakai $request->only(['name','email','password'])
[ ] role diisi eksplisit di controller, BUKAN dari input form
[ ] update() tidak mengizinkan perubahan role dari form
```

### Checklist CI GitHub Actions

```yaml
# .github/workflows/ci.yml (minimal)
name: CI
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
      - run: composer install --no-interaction
      - run: cp .env.example .env && php artisan key:generate
      - run: php artisan migrate:fresh --seed   # harus hijau
      - run: php artisan migrate:refresh        # harus hijau
```

```
[ ] .github/workflows/ci.yml ada dan aktif
[ ] Pipeline hijau di GitHub Actions
[ ] .env tidak ter-commit (ada di .gitignore)
[ ] Setiap anggota punya ≥1 commit atas namanya sendiri
[ ] Setiap fitur masuk lewat PR yang direview anggota lain
```

---

## Poin yang Akan Ditanyakan Saat Interview (Kuis 3)

1. **Migrasi:** Kenapa `grades` harus dibuat setelah `submissions`?
   Apa yang terjadi kalau urutannya terbalik?

   > 🔍 **Petunjuk arah:**
   > Buka file migrasi `grades` di `database/migrations/`. Cari baris yang
   > menggunakan `->constrained(...)`. Tabel apa yang direferensikan?
   > Lalu lihat urutan timestamp nama file migrasi — mana yang lebih dulu dieksekusi?
   > Coba sendiri: jalankan `php artisan migrate` di branch `w03` (yang urutannya
   > sengaja terbalik) dan baca pesan error-nya. Pesan itu akan menjelaskan
   > mengapa urutannya penting lebih baik dari jawaban mana pun.

2. **Constraint:** Kenapa `course_user` butuh unique composite, bukan unique
   di masing-masing kolom saja?

   > 🔍 **Petunjuk arah:**
   > Buka `database/migrations/*_create_course_user_table.php`. Pikirkan: kalau
   > `course_id` saja yang di-unique, apa artinya? (Satu mata kuliah hanya boleh
   > punya satu mahasiswa — jelas salah.) Kalau `user_id` saja yang di-unique?
   > (Satu mahasiswa hanya boleh ikut satu MK seumur hidup — jelas salah juga.)
   > Lalu apa artinya *kombinasi* keduanya yang di-unique?
   > Untuk membuktikannya: jalankan script di `eksperimen/break_all.php` bagian
   > BREAK 1 dan lihat sendiri apa yang terjadi saat constraint itu tidak ada.

3. **onDelete:** Bedakan kapan `cascade` vs `restrict` dipakai. Beri contoh
   konkret dari skema KampusLMS.

   > 🔍 **Petunjuk arah:**
   > Buka `database/migrations/*_create_courses_table.php` dan
   > `*_create_materials_table.php`. Bandingkan perilaku `onDelete` pada
   > `lecturer_id` vs `course_id` di materials.
   > Lalu tanyakan diri sendiri: *kalau seorang dosen dihapus, apakah wajar
   > semua mata kuliahnya ikut terhapus?* Bandingkan dengan: *kalau sebuah MK
   > dihapus, apakah wajar semua materinya ikut terhapus?*
   > Jalankan BREAK 5 di `eksperimen/break_all.php` untuk melihat efek nyata
   > `cascadeOnDelete` pada `lecturer_id` — berapa data yang hilang dalam satu
   > perintah `delete()`.

4. **Mass assignment:** Tunjukkan perintah Tinker yang membuktikan bahwa
   `$guarded = []` berbahaya. Apa yang mencegahnya — CSRF atau `$fillable`?

   > 🔍 **Petunjuk arah:**
   > Buka `app/Models/User.php` di branch `w03`. Cari `$guarded`.
   > Lalu buka `app/Http/Controllers/UserController.php` dan cari bagaimana
   > `store()` memanggil `User::create()` — data apa yang diteruskan?
   > Setelah itu jalankan perintah Tinker ini dan amati hasilnya:
   > ```bash
   > php artisan tinker
   > ```
   > ```php
   > App\Models\User::create([
   >     'name' => 'Coba', 'email' => 'coba@test.test',
   >     'password' => 'rahasia', 'role' => 'admin',
   > ]);
   > App\Models\User::where('email','coba@test.test')->value('role');
   > ```
   > Untuk menjawab "CSRF atau `$fillable`?": baca kembali bagian BREAK 2 di
   > `eksperimen/break_all.php` yang menjelaskan dua ancaman berbeda yang
   > dicegah oleh dua mekanisme berbeda.

5. **down():** Apa yang terjadi pada pipeline CI jika `down()` dikosongkan?

   > 🔍 **Petunjuk arah:**
   > Buka `database/migrations/*_create_assignments_table.php` di branch `w03`.
   > Lihat isi method `down()`. Lalu jalankan dua perintah ini secara berurutan:
   > ```bash
   > php artisan migrate        # berhasil
   > php artisan migrate:refresh  # apa yang terjadi?
   > ```
   > Baca pesan error-nya dengan teliti. CI otomatis menjalankan `migrate:refresh`
   > pada setiap push — kalau perintah itu gagal, seluruh pipeline merah dan
   > tidak ada PR yang bisa di-merge. Mengapa?

6. **`$request->all()` vs `$request->only()`:** Kapan masing-masing dipakai?
   Mana yang lebih aman dan mengapa?

   > 🔍 **Petunjuk arah:**
   > Buka `app/Http/Controllers/UserController.php` di branch `w03`. Cari
   > semua tempat di mana `$request->all()` dipakai.
   > Lalu buka dokumentasi Laravel atau jalankan di Tinker:
   > ```php
   > // Simulasikan request dengan field liar
   > $fakeRequest = ['name'=>'A','email'=>'b@b.test','password'=>'x','role'=>'admin'];
   > // Apa yang dikembalikan all() vs only(['name','email','password'])?
   > ```
   > Kunci pertanyaannya: siapa yang menentukan kolom mana yang masuk database —
   > pengguna yang mengirim request, atau developer yang menulis kode?
   > Hubungkan jawaban ini kembali dengan `$fillable` di pertanyaan 4.

---

*Dokumen ini mendampingi `BUGS_W03.md` yang berisi penjelasan teknis
setiap bug di branch `w03`.*
