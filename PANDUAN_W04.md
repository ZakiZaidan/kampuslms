# 📖 Week 4 — Panduan Mengajar: READ · BREAK · FIX · BUILD

> Panduan ini mendampingi branch `w04` (kode rusak untuk bagian FIX).
> Berisi penjelasan konsep, arti istilah asing, alur praktikum, dan
> antisipasi pertanyaan mahasiswa.

---

## Glosarium — Istilah Asing Minggu 4

| Istilah | Artinya dalam Bahasa Sederhana |
|---------|-------------------------------|
| **Stateless** | Tidak punya ingatan. HTTP tidak tahu bahwa request ke-2 berasal dari orang yang sama dengan request ke-1. Setiap request adalah perkenalan baru. |
| **State** | Informasi yang perlu diingat antar request. Contoh: siapa yang login, apa filter yang aktif, pesan apa yang harus ditampilkan. |
| **Session** | Tempat menyimpan state di server, ditautkan ke browser lewat cookie. Hidup selama browser terbuka. Seperti "kartu nama" yang disimpan server. |
| **Flash Session** | Session yang otomatis hilang setelah dibaca satu kali. Cocok untuk pesan sukses/gagal setelah redirect. |
| **Query String** | Bagian URL setelah `?`. Contoh: `/courses?q=basis+data&status=active`. Terlihat di URL, bisa dibagikan dan di-bookmark. |
| **Cookie** | Data kecil yang disimpan di browser pengguna. Server bisa membaca dan menulis cookie. |
| **PRG** | Post → Redirect → Get. Pola desain untuk mencegah pengiriman ulang form saat F5. |
| **Form Resubmission** | Pengiriman ulang form. Terjadi saat pengguna menekan F5 setelah melakukan POST. Penyebab data duplikat. |
| **CSRF** | Cross-Site Request Forgery. Serangan di mana situs jahat membuat browser Anda mengirim request ke aplikasi lain tanpa sepengetahuan Anda. |
| **CSRF Token** | Kode rahasia yang disisipkan di form. Diverifikasi server. Memastikan request berasal dari halaman yang sah. |
| **Form Request** | Kelas PHP tersendiri untuk mengelola validasi dan otorisasi form. Memisahkan validasi dari controller. |
| **Validation** | Pemeriksaan data yang dikirim pengguna sebelum disimpan ke database. |
| **`validated()`** | Method yang mengembalikan hanya field yang lolos aturan validasi. Mencegah mass assignment. |
| **`old()`** | Helper Blade untuk mengambil nilai input dari request sebelumnya yang gagal validasi. Agar pengguna tidak perlu mengetik ulang. |
| **`@error`** | Direktif Blade untuk menampilkan pesan error validasi per field. |
| **Eager Loading** | Memuat relasi sekaligus dalam satu query. `with('lecturer')` = ambil semua dosen sekaligus, bukan satu per baris (N+1). |
| **N+1 Query** | Bug performa: 1 query untuk daftar + N query untuk setiap relasi. Dengan 15 MK = 16 query. Dengan `with()` = 2 query. |
| **Pagination** | Pembagian data menjadi halaman-halaman. `paginate(15)` = tampilkan 15 data per halaman. |
| **`withQueryString()`** | Method agar tautan pagination membawa semua query parameter yang sedang aktif (filter, pencarian). |
| **`exists:tabel,kolom`** | Aturan validasi: nilai harus ada di kolom tersebut dalam tabel tersebut. |
| **`in:nilai1,nilai2`** | Aturan validasi: nilai harus salah satu dari daftar yang ditentukan. |
| **`Rule::unique()->ignore()`** | Validasi unique yang mengabaikan baris tertentu. Wajib dipakai saat update agar MK tidak menolak kodenya sendiri. |
| **`abort_if` / `abort_unless`** | Helper untuk menghentikan request dengan HTTP error tertentu (403, 404, dll.). |

---

## Konsep 1 — State: Di Mana Menyimpannya?

**Analogi loker:**

> "Bayangkan kafe kampus. HTTP seperti pelayan yang amnesia — setiap kali Anda
> datang, pelayan tidak ingat siapa Anda atau apa yang Anda pesan sebelumnya.
> Session seperti kartu nama yang Anda simpan di loker kafe. Setiap kunjungan,
> Anda tunjukkan kartu → pelayan tahu ini Anda. Cookie adalah kuncinya."

**Analogi URL:**

> "Query string seperti catatan tempel di amplop surat. Siapa pun yang memegang
> amplop bisa membacanya. Kalau Anda mau mengirim filter pencarian ke teman,
> session tidak bisa — teman tidak punya session Anda. Tapi URL bisa dikopi."

### 📊 Tabel yang Wajib Ditampilkan di Papan

| State | Di mana disimpan | Bisa dibagikan? | Cocok untuk |
|-------|-----------------|-----------------|-------------|
| Filter/pencarian | Query string (`?q=...`) | ✅ Ya | Filter, halaman, urutan |
| Identitas login | Session | ❌ Tidak | Siapa yang login |
| Pesan sukses/gagal | Flash session | ❌ Tidak | Setelah redirect |
| Preferensi tampilan | Cookie | ❌ Tidak | Dark mode, bahasa |

**Pertanyaan pemantik:**
> "Kalau filter pencarian disimpan di session dan dua mahasiswa pakai laptop yang sama
> bergantian, apa yang terjadi?"

---

## Konsep 2 — PRG: Post → Redirect → Get

**Demo langsung (wajib dilakukan):**

1. Buat halaman form sederhana yang `return view()` setelah POST
2. Isi form → submit → muncul halaman hasil
3. Tekan F5
4. Browser menampilkan dialog "Confirm Form Resubmission"
5. Klik OK → data duplikat

> "Anda pernah melihat kotak dialog ini tapi tidak tahu artinya? Sekarang Anda
> tahu: aplikasi Anda tidak memakai PRG, dan setiap F5 berpotensi duplikat data."


```
❌ Tanpa PRG:
POST /courses → simpan → return view (URL masih POST)
F5 → kirim ulang POST → duplikat data

✅ Dengan PRG:
POST /courses → simpan → redirect 302 → GET /courses/5
F5 → kirim ulang GET → aman, tidak ada yang tersimpan
```

### ❓ Pertanyaan Pemantik
- "Kenapa F5 di halaman GET aman tapi F5 di halaman POST berbahaya?"
- "Apa yang terjadi kalau Anda `return view()` di method `store()` dan pengguna menekan tombol Back lalu submit lagi?"

---

## Konsep 3 — CSRF

**Skenario serangan:**

> "Bayangkan Anda sudah login di KampusLMS. Sambil itu, Anda membuka email spam
> yang berisi link. Anda klik link itu. Halaman jahat itu punya form tersembunyi
> yang secara diam-diam mengirim POST ke KampusLMS atas nama Anda — menghapus
> mata kuliah, mengubah nilai, dll. Browser Anda yang melakukannya, bukan hacker.
> Karena cookie session Anda ada, server mengira itu Anda."

> "Token CSRF mencegah ini: setiap form punya kode rahasia yang hanya diketahui
> server dan halaman itu. Situs jahat tidak bisa tahu kode ini."

**Demo cepat:**
Hapus `@csrf` dari form create → submit → tampilkan error 419.

```
HTTP 419 Page Expired
= Laravel mendeteksi tidak ada CSRF token
= Request ditolak sebelum sampai ke controller
```

**Yang penting disampaikan:**
- `@csrf` menambahkan `<input type="hidden" name="_token" value="...">` di form
- Laravel memverifikasi token ini di setiap POST/PUT/PATCH/DELETE
- Ini BERBEDA dari mass assignment protection — dua mekanisme untuk dua ancaman berbeda

---

## Konsep 4 — Form Request & Validasi

**Masalah validasi di JavaScript:**

> "Ini yang sering salah sangka: validasi HTML `required` dan JavaScript adalah
> untuk kenyamanan pengguna, bukan keamanan. Bisa dimatikan dalam 5 detik di DevTools,
> atau dilewati dengan curl. Validasi sesungguhnya harus di server."

**Demo: melewati validasi frontend:**
```bash
# Kirim tanpa membuka browser sama sekali:
curl -X POST http://kampuslms.test/courses -b cookies.txt \
  -d "_token=$TOKEN" -d "code=XX01" -d "name=Uji" -d "sks=99" \
  -d "lecturer_id=99999" -d "status=superadmin"
```

**Kenapa Form Request, bukan validasi di controller?**

```php
// ❌ Validasi di controller — berantakan dan tidak bisa diuji:
public function store(Request $request)
{
    $validated = $request->validate([
        'code' => ['required', 'unique:courses,code'],
        // 10 aturan lagi...
    ]);
    Course::create($validated);
}

// ✅ Form Request — terpisah, bersih, bisa diuji terpisah:
public function store(StoreCourseRequest $request)
{
    Course::create($request->validated());
}
```

**Jebakan `unique` pada update:**

> "Ini masalah yang hampir semua orang temui pertama kali. Coba edit mata kuliah
> tanpa mengubah kodenya. Hasilnya: 'Kode ini sudah dipakai!' — oleh dirinya sendiri.
> Kenapa? Karena `unique` memeriksa seluruh tabel, termasuk baris MK itu sendiri.
> Solusi: `Rule::unique()->ignore($this->course)` — abaikan baris ini saat mengecek."

---

## Konsep 5 — Pagination dengan Filter

**Demonstrasi `withQueryString()`:**

1. Cari "pemrograman" → tampil 18 hasil, 15 per halaman
2. Tanpa `withQueryString()`: klik halaman 2 → URL menjadi `/courses?page=2` → filter hilang
3. Dengan `withQueryString()`: klik halaman 2 → URL menjadi `/courses?q=pemrograman&page=2` → filter tetap

> "Ini bug yang paling sering dilaporkan mahasiswa di aplikasi kampus nyata.
> Pengguna mencari → klik halaman 2 → hasil pencarian hilang → pengguna
> mengira aplikasinya rusak."

**N+1 Query:**
```php
// ❌ Tanpa eager loading — 16 query untuk 15 MK:
$courses = Course::paginate(15);
// Di view: $course->lecturer->name → query per baris

// ✅ Dengan eager loading — 2 query untuk 15 MK:
$courses = Course::with('lecturer')->paginate(15);
```

---

## READ — 6 Pertanyaan untuk Dijawab

**Instruksi untuk mahasiswa:**
> "Buka form tambah mata kuliah. Isi SKS dengan 99. Submit. Lalu jawab enam
> pertanyaan ini TANPA AI — hanya dari DevTools, kode, dan pengamatan Anda."

1. Method PHP apa yang menerima request ini? Di controller mana?
2. Validasi terjadi di mana persisnya — sebelum atau sesudah baris pertama method?
3. Ke mana Laravel me-redirect setelah validasi gagal? Siapa yang menentukan tujuannya?
4. Dari mana `@error('sks')` mengambil pesannya?
5. Dari mana `old('sks')` mengambil nilainya? Berapa lama nilainya tersimpan?
6. Buka DevTools → Application → Cookies. Temukan cookie session Laravel. Catat namanya.

---

## BREAK — 7 Kerusakan

mahasiswa **tulis prediksi dulu** sebelum menjalankan.

### 💥 BREAK 1 — Hapus `@csrf`

```blade
{{-- Di form create, hapus: --}}
@csrf
```

```bash
php artisan serve
# Buka form → submit → lihat error 419
# Baca pesan errornya: "Page Expired"
```

**Yang diamati:** 419 langsung, sebelum controller dieksekusi.

---

### 💥 BREAK 2 — `$request->all()` bukan `$request->validated()`

```php
// Di CourseController@store, ubah:
Course::create($request->validated());
// Menjadi:
Course::create($request->all());
```

```bash
# Kirim field liar:
curl -X POST http://kampuslms.test/courses -b cookies.txt \
  -d "_token=$TOKEN" -d "code=TEST01" -d "name=Uji" -d "sks=3" \
  -d "lecturer_id=1" -d "status=active" \
  -d "field_tidak_ada=nilai_berbahaya"
```

**Yang diamati:** Field liar masuk ke `create()`. Kalau model tidak ketat, bisa tersimpan.

---

### 💥 BREAK 3 — Hapus `exists:users,id`

```php
// Di StoreCourseRequest:
'lecturer_id' => ['required'],  // hapus exists:users,id
```

```bash
curl -X POST http://kampuslms.test/courses -b cookies.txt \
  -d "_token=$TOKEN" -d "code=YY01" -d "name=Uji" -d "sks=3" \
  -d "lecturer_id=99999" -d "status=active"
```

**Yang diamati:** Data masuk dengan `lecturer_id=99999` yang tidak ada di database.
Buka Tinker: `Course::find(id)->lecturer` → null → di view akan throw error.

---

### 💥 BREAK 4 — Hapus `in:` pada status

```php
'status' => ['required', 'string'],  // hapus in:draft,active,archived
```

```bash
curl -X POST ... -d "status=superadmin"
# → Data masuk dengan status='superadmin'
```

---

### 💥 BREAK 5 — Hapus `->withQueryString()`

```php
->paginate(15)  // tanpa ->withQueryString()
```

Seeder minimal dulu:
```bash
php artisan tinker --execute="App\Models\Course::factory(20)->create();"
```

Lalu: cari → klik halaman 2 → lihat filter hilang.

---

### 💥 BREAK 6 — `return view()` di `store()`

```php
public function store(StoreCourseRequest $request)
{
    $course = Course::create($request->validated());
    return view('courses.index', [...]);  // bukan redirect
}
```

Submit form → F5 → dialog resubmission → OK → data duplikat.

---

### 💥 BREAK 7 — Hapus `old()` dari input

```blade
{{-- Sebelum: --}}
<input type="text" name="name" value="{{ old('name') }}">

{{-- Sesudah: --}}
<input type="text" name="name">
```

Isi form dengan banyak field → buat satu field invalid → submit.
**Yang diamati:** Semua field kosong. Harus isi ulang dari awal. Rasakan frustrasinya sebagai pengguna.

---

## FIX — Instruksi untuk Mahasiswa (30 menit)

```bash
git clone https://github.com/ZakiZaidan/kampuslms.git -b w04
cd kampuslms
composer install && npm install
cp .env.example .env && php artisan key:generate
# (atur DB di .env)
php artisan migrate:fresh
```

atau hanya copy file yang ada bugsnya saja lalu tempel di repo kalian (atau dowload zip bisa juga bebas aja)

**6 bug yang harus ditemukan dan diperbaiki:**

1. `StoreCourseRequest` — `lecturer_id` tanpa `exists`, `status` tanpa `in:`
2. `UpdateCourseRequest` — `unique` tanpa `->ignore()`
3. `CourseController@index` — filter di session, bukan query string
4. `CourseController@index` — `withQueryString()` hilang
5. `CourseController@store` — tidak redirect (PRG dilanggar)
6. `courses/edit.blade.php` — `@csrf` hilang

**Yang dinilai dari PR:**
Deskripsi PR wajib menjelaskan **dampak dari sudut pandang pengguna DAN dari
sudut pandang penyerang** untuk setiap bug.

---

## BUILD — Checklist Deliverable

```
[ ] StoreCourseRequest + UpdateCourseRequest dengan pesan bahasa Indonesia
[ ] UpdateCourseRequest pakai Rule::unique()->ignore($this->course)
[ ] Form create: ada @csrf, old(), @error di setiap field
[ ] Form edit: ada @csrf, old() dengan fallback nilai saat ini
[ ] Flash message di layout (berlaku untuk seluruh halaman)
[ ] CourseController@index: filter dari query string, bukan session
[ ] CourseController@index: ->withQueryString() ada
[ ] CourseController@store: redirect setelah simpan (PRG)
[ ] Hal yang sama untuk modul User
[ ] Konfirmasi sebelum hapus
```

---

## Antisipasi Pertanyaan Mahasiswa

### ❓ "Kenapa error 419? Saya kan sudah login."

> Error 419 bukan soal login, tapi soal CSRF token. Token ini memastikan form
> yang di-submit berasal dari halaman Anda, bukan dari situs lain. Kalau token
> tidak ada (lupa `@csrf`) atau sudah kedaluwarsa (tab terbuka terlalu lama),
> Laravel menolak dengan 419.

### ❓ "Kenapa `old()` perlu? Kan validasi sudah muncul errornya."

> Coba sendiri: isi form panjang dengan 10 field → satu field salah → submit →
> validasi gagal → semua field kosong. Pengguna harus mengisi ulang dari awal.
> Ini UX yang buruk. `old()` mengisi kembali nilai yang sudah diketik sebelumnya
> hanya dari field yang tidak bermasalah.

### ❓ "Apa bedanya `$request->validated()` dan `$request->only([...])`?"

> - `$request->only([...])` = ambil field tertentu dari request (input yang dikirim pengguna)
> - `$request->validated()` = ambil field yang sudah lolos aturan validasi (sudah diverifikasi)
>
> `validated()` lebih aman karena data sudah dibersihkan dan hanya berisi
> field yang memang ada di `rules()`. Gunakan `validated()` setelah ada Form Request.

### ❓ "Kenapa filter pencarian tidak boleh disimpan di session?"

> Tiga alasan nyata:
> 1. **Dua tab** — Tab A cari X, Tab B cari Y → session saling timpa
> 2. **Berbagi URL** — URL `/courses` tidak mengandung filter, jadi tidak bisa dikopi ke teman
> 3. **Tombol Back** — Kembali dari halaman detail → filter hilang karena session sudah berubah

### ❓ "Kenapa unique pada update berbeda dengan unique pada create?"

> Saat create: pastikan kode belum ada di tabel mana pun → `unique:courses,code`
>
> Saat update: pastikan kode belum dipakai MK LAIN — tapi boleh sama dengan kode
> MK yang sedang diedit → `Rule::unique('courses', 'code')->ignore($this->course)`
>
> Tanpa `ignore()`, MK tidak akan bisa disimpan kecuali kode-nya diubah setiap kali edit.

---

## Poin Interview Kuis 4

| Pertanyaan | Jawaban yang Diharapkan |
|------------|------------------------|
| Kenapa validasi JS tidak dianggap keamanan? | Bisa dimatikan di DevTools atau dilewati dengan curl — tidak ada di server |
| Apa yang dikembalikan `$request->validated()`? | Hanya field yang lolos aturan validasi — tidak termasuk field yang tidak ada di `rules()` |
| Jelaskan PRG | POST → simpan data → redirect ke GET → F5 aman |
| Kenapa filter di query string, bukan session? | URL bisa dibagikan, dua tab tidak saling ganggu, Back bekerja |
| Apa itu CSRF? | Serangan situs jahat yang memanfaatkan cookie session korban untuk mengirim request |
| Kenapa `unique` pada update butuh `ignore()`? | Agar kode MK itu sendiri tidak dianggap duplikat saat disimpan |

---
