# 🔴 Week 9 — Kode Rusak yang Disengaja (Branch `w09`)

> **Tujuan:** Mahasiswa menemukan dan memperbaiki 7 bug dalam implementasi
> file storage — meliputi kebocoran berkas, IDOR berkas, nama berkas berbahaya,
> validasi palsu, satuan salah, berkas yatim, dan otorisasi semu di Livewire.

---

## Peta Bug

| # | Kategori | File | Gejala yang Terlihat |
|---|----------|------|----------------------|
| 1 | Disk publik — submission bocor | `SubmissionController@store` | Berkas tugas bisa diakses URL langsung tanpa login |
| 2 | Tanpa `Gate::authorize()` | `SubmissionController@download` | Mahasiswa A bisa unduh submission milik Mahasiswa B (IDOR berkas) |
| 3 | `storeAs()` dengan nama asli | `SubmissionController@store`, `MaterialController@store` | Dua mahasiswa upload `tugas.pdf` — berkas pertama tertimpa |
| 4 | Validasi `extensions` bukan `mimes` | `StoreSubmissionRequest`, `StoreMaterialRequest` | File PHP berekstensi `.pdf` lolos validasi |
| 5 | Satuan `max` salah | `StoreSubmissionRequest`, `StoreMaterialRequest` | `max:5` yang dimaksud 5 MB hanya mengizinkan 5 KB |
| 6 | Tidak ada hapus berkas fisik | `app/Models/Submission.php`, `app/Models/Material.php` | Hapus record dari DB — berkas fisik di storage tetap ada (yatim) |
| 7 | Livewire otorisasi semu | `resources/views/livewire/submission-list.blade.php` | Tombol download disembunyikan `@if`, tapi method `download()` tidak ada `Gate::authorize()` |

---

## Bug 1 — Disk Publik: Berkas Submission Bocor

### File: `app/Http/Controllers/SubmissionController.php`

### Gejala
```
1. Mahasiswa A mengumpulkan tugas, berkas tersimpan
2. Buka jendela penyamaran (tanpa login)
3. Ketik URL: http://127.0.0.1:8000/storage/submissions/nama-berkas.pdf
4. -> 200: berkas terbuka! Tugas seluruh kelas bisa diakses publik
```

### Penjelasan

```php
// BUG — disimpan ke disk 'public', bisa diakses URL langsung:
public function store(StoreSubmissionRequest $request, Assignment $assignment)
{
    // disk('public') = storage/app/public/ = symlink ke public/storage/
    $path = $request->file('file')->store('submissions', 'public');

    Submission::updateOrCreate(
        ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
        ['file_path' => $path, 'is_late' => now()->isAfter($assignment->deadline)]
    );
}
```

Setelah `php artisan storage:link`, siapa pun yang menebak nama berkas
bisa mengakses `http://domain/storage/submissions/nama-berkas.pdf` tanpa login.

### Perbaikan

```php
// BENAR — disk 'local' (default), tidak ada URL publik:
$path = $request->file('file')->store('submissions');
// atau eksplisit:
$path = $request->file('file')->store('submissions', 'local');

// Untuk mengunduh, harus lewat controller:
public function download(Submission $submission): StreamedResponse
{
    Gate::authorize('view', $submission);
    abort_unless(Storage::exists($submission->file_path), 404);
    return Storage::download($submission->file_path, $submission->original_name);
}
```

**Aturan:**
- Berkas privat (submission, nilai, dokumen identitas) → disk `local`
- Berkas publik (foto profil, thumbnail produk) → disk `public`
- Keduanya TIDAK boleh dicampur

---

## Bug 2 — Tanpa `Gate::authorize()`: IDOR Versi Berkas

### File: `app/Http/Controllers/SubmissionController.php`

### Gejala
```
1. Login sebagai Mahasiswa A
2. Ketik URL: /mahasiswa/submissions/99/download (id milik Mahasiswa B)
3. -> 200: berkas submission Mahasiswa B terunduh!
   Padahal tidak ada hubungan antara Mahasiswa A dan Submission #99
```

### Penjelasan

```php
// BUG — Route Model Binding menemukan submission, langsung download:
public function download(Submission $submission): StreamedResponse
{
    // Tidak ada cek: apakah user ini boleh mengakses submission ini?
    abort_unless(Storage::exists($submission->file_path), 404);
    return Storage::download($submission->file_path, $submission->original_name);
}
```

Ini adalah IDOR (Insecure Direct Object Reference) yang sama dengan minggu 5,
tetapi kali ini berlaku pada berkas fisik. Route Model Binding menemukan record
berdasarkan ID — bukan berdasarkan kepemilikan.

### Perbaikan

```php
use Illuminate\Support\Facades\Gate;

public function download(Submission $submission): StreamedResponse
{
    Gate::authorize('view', $submission);  // <- wajib, melempar 403 jika ditolak

    abort_unless(Storage::exists($submission->file_path), 404);
    return Storage::download($submission->file_path, $submission->original_name);
}
```

```php
// app/Policies/SubmissionPolicy.php
public function view(User $user, Submission $submission): bool
{
    return $user->role === 'admin'
        || $user->id === $submission->user_id                          // pemilik
        || $submission->assignment->course->lecturer_id === $user->id; // dosen pengampu
}
```

**Aturan yang tidak boleh dilanggar:**
- Setiap endpoint yang mengembalikan berkas privat HARUS ada `Gate::authorize()`
- Route Model Binding hanya menjamin record ditemukan — bukan bahwa user boleh mengaksesnya

---

## Bug 3 — `storeAs()` dengan Nama Asli: Berkas Tertimpa

### File: `app/Http/Controllers/SubmissionController.php` dan `MaterialController.php`

### Gejala
```
1. Mahasiswa A kumpulkan tugas: file bernama "tugas.pdf"
   -> disimpan sebagai: storage/app/submissions/tugas.pdf

2. Mahasiswa B kumpulkan tugas: file bernama "tugas.pdf" (isi berbeda)
   -> disimpan sebagai: storage/app/submissions/tugas.pdf  <- MENIMPA!

3. php artisan tinker:
   Submission::find(1)->file_path === Submission::find(2)->file_path
   -> true  (keduanya tugas.pdf — berkas mahasiswa A sudah hilang)
```

### Penjelasan

```php
// BUG — nama dari user dipakai sebagai nama fisik:
$originalName = $request->file('file')->getClientOriginalName();
$path = $request->file('file')->storeAs('submissions', $originalName);
// -> submissions/tugas.pdf

// Jika user lain mengunggah file bernama sama:
// -> submissions/tugas.pdf (file lama ditimpa!)
```

Ada dua risiko:
1. **File tertimpa** — dua user nama file sama → data hilang
2. **Path traversal** — nama file `../../.env` → file berbahaya tersimpan di luar folder yang dimaksud

### Perbaikan

```php
// BENAR — Laravel membuat nama UUID acak:
$path = $request->file('file')->store('submissions');
// -> submissions/3f8a9b2c-1d4e-5f6a-7b8c-9d0e1f2a3b4c.pdf

// Simpan nama asli HANYA di database, untuk ditampilkan ke user:
Submission::updateOrCreate(
    ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
    [
        'file_path'     => $path,
        'original_name' => $request->file('file')->getClientOriginalName(),
        'file_size'     => $request->file('file')->getSize(),
        'is_late'       => now()->isAfter($assignment->deadline),
    ]
);
```

---

## Bug 4 — Validasi `extensions` Bukan `mimes`: File Berbahaya Lolos

### File: `app/Http/Requests/StoreSubmissionRequest.php`, `StoreMaterialRequest.php`

### Gejala
```
1. Buat file PHP: echo "<?php phpinfo(); ?>" > malware.php
2. Ganti nama:    mv malware.php tugas.pdf
3. Upload dengan validasi 'extensions:pdf'
   -> 200: berhasil diunggah! Laravel tidak memeriksa isi file
4. Jika server misconfigured: file PHP ini bisa dieksekusi
```

### Penjelasan

```php
// BUG — hanya memeriksa string ekstensi nama file:
'file' => ['required', 'file', 'extensions:pdf,doc,docx,ppt,pptx', 'max:20480'],
```

Validasi `extensions` hanya melihat bagian setelah titik terakhir di nama file.
Pengguna bisa menulis nama apa pun — `malware.php` menjadi `tugas.pdf` hanya dengan `mv`.

```php
// BENAR — memeriksa byte pertama file (magic bytes):
'file' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
```

Validasi `mimes` membaca signature byte berkas menggunakan fungsi `finfo_file()` PHP.
File PHP yang diganti nama tetap terdeteksi sebagai `text/x-php`, bukan `application/pdf`.

### Perbaikan

```php
// app/Http/Requests/StoreMaterialRequest.php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'file'  => ['required_without:url', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'],
        'url'   => ['required_without:file', 'nullable', 'url'],
    ];
}

// app/Http/Requests/StoreSubmissionRequest.php
public function rules(): array
{
    return [
        'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
    ];
}
```

**Tabel ringkasan:**

| Validasi | Cek apa | Bisa dipalsukan |
|----------|---------|-----------------|
| `extensions:pdf` | String ekstensi nama file | Ya — ganti nama saja |
| `mimes:pdf` | Byte pertama file (magic bytes) | Tidak — isi tidak bisa bohong |

---

## Bug 5 — Satuan `max` Salah: 5 KB Bukan 5 MB

### File: `app/Http/Requests/StoreSubmissionRequest.php`, `StoreMaterialRequest.php`

### Gejala
```
1. Coba unggah file PDF 200 KB
2. -> 422 Unprocessable: "The file must not be greater than 5 kilobytes."
3. File yang sangat kecil pun ditolak

Atau sebaliknya:
1. Validasi max:20 (pikir 20 MB)
2. -> File 50 KB berhasil diunggah (20 KB saja yang diizinkan)
```

### Penjelasan

```php
// BUG — satuan max adalah KILOBYTE, bukan megabyte:
'file' => ['required', 'file', 'mimes:pdf', 'max:5'],     // 5 KB
'file' => ['required', 'file', 'mimes:pdf,doc', 'max:20'], // 20 KB
```

Banyak developer berpikir `max:5` berarti 5 MB. Dokumentasi Laravel menyebutkan
bahwa satuan untuk rule `max` pada file adalah **kilobytes (KB)**.

### Perbaikan

```php
// Konversi yang benar:
// 5 MB   = 5 * 1024 KB   = 5120
// 10 MB  = 10 * 1024 KB  = 10240
// 20 MB  = 20 * 1024 KB  = 20480

// app/Http/Requests/StoreSubmissionRequest.php
'file' => ['required', 'file', 'mimes:pdf', 'max:5120'],   // 5 MB

// app/Http/Requests/StoreMaterialRequest.php
'file' => ['required_without:url', 'nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:20480'], // 20 MB
```

**Ingat juga:** `upload_max_filesize` di `php.ini` harus lebih besar dari batas ini.
Jika `upload_max_filesize = 2M` dan validasi `max:5120`, PHP akan memblokir file > 2MB
sebelum Laravel sempat menjalankan validasi — user mendapat error kosong/500, bukan pesan validasi.

---

## Bug 6 — Tidak Ada Hapus Berkas Fisik: Berkas Yatim

### File: `app/Models/Submission.php`, `app/Models/Material.php`

### Gejala
```
# Sebelum hapus:
ls storage/app/submissions/ -> 5 file

# Hapus dari database:
php artisan tinker -e "Submission::find(1)->forceDelete()"

# Sesudah hapus:
ls storage/app/submissions/ -> masih 5 file!
File ke-1 menjadi "yatim": ada di storage, tidak bisa diakses siapa pun
```

### Penjelasan

```php
// BUG — hanya menghapus record database, berkas fisik dibiarkan:
public function destroy(Submission $submission)
{
    Gate::authorize('delete', $submission);
    $submission->delete();
    // Storage::delete($submission->file_path) <- tidak ada!
    return redirect()->back()->with('success', 'Submission dihapus.');
}
```

Masalah lebih dalam: jika penghapusan terjadi via cascade (hapus Assignment → hapus Submission),
event `deleting` pada model tidak terpanggil jika menggunakan cascade di database level.

### Perbaikan

**Opsi 1 — Model observer (direkomendasikan, menangkap semua kasus):**
```php
// app/Models/Submission.php
use Illuminate\Support\Facades\Storage;

protected static function booted(): void
{
    static::deleting(function (Submission $submission) {
        Storage::delete($submission->file_path);
    });
}

// app/Models/Material.php
protected static function booted(): void
{
    static::deleting(function (Material $material) {
        if ($material->file_path) {
            Storage::delete($material->file_path);
        }
    });
}
```

**Penting — masalah cascade database vs Eloquent:**
```php
// Jika migrasi memakai onDelete('cascade') di database:
// -> PHP model observer TIDAK dipanggil saat cascade terjadi di DB level
// -> Berkas tetap yatim

// Solusinya: hapus child records lewat Eloquent sebelum hapus parent:
// app/Models/Assignment.php
protected static function booted(): void
{
    static::deleting(function (Assignment $assignment) {
        // Hapus tiap submission lewat Eloquent agar observer terpanggil:
        $assignment->submissions()->each(fn($s) => $s->delete());
    });
}
```

---

## Bug 7 — Livewire Otorisasi Semu: Tombol Tersembunyi, Method Tidak Aman

### File: `resources/views/livewire/submission-list.blade.php`, `app/Livewire/SubmissionList.php`

### Gejala
```
1. Login sebagai Mahasiswa A
2. Halaman daftar submission hanya menampilkan tombol download untuk milik sendiri
   (tombol milik Mahasiswa B tersembunyi) <- @if bekerja
3. Tapi kirim request langsung ke Livewire action:
   curl -X POST /livewire/update -d "fingerprint=...&action=download&submissionId=99"
4. -> File submission Mahasiswa B terunduh! <- IDOR via Livewire
```

### Penjelasan

```blade
{{-- BUG — hanya menyembunyikan tampilan, tidak mengamankan action: --}}
@foreach($submissions as $submission)
    @if($submission->user_id === auth()->id())
        <button wire:click="download({{ $submission->id }})">Unduh</button>
    @endif
@endforeach
```

```php
// BUG — method Livewire tidak ada pengecekan otorisasi:
class SubmissionList extends Component
{
    public function download(int $submissionId): StreamedResponse
    {
        $submission = Submission::findOrFail($submissionId);
        // Gate::authorize('view', $submission) <- tidak ada!
        return Storage::download($submission->file_path, $submission->original_name);
    }
}
```

`@if` di Blade hanya menyembunyikan tombol dari tampilan — sama persis dengan
masalah `@can` vs `Gate::authorize()` dari minggu 7, tetapi kali ini di konteks Livewire.

### Perbaikan

```php
// app/Livewire/SubmissionList.php
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

public function download(int $submissionId): StreamedResponse
{
    $submission = Submission::findOrFail($submissionId);

    Gate::authorize('view', $submission);  // <- wajib ada di method Livewire juga

    abort_unless(Storage::exists($submission->file_path), 404);

    return Storage::download($submission->file_path, $submission->original_name);
}
```

**Aturan untuk Livewire:**
- `@if` / `@can` di template → hanya UX (menyembunyikan tombol)
- `Gate::authorize()` di method PHP → keamanan sesungguhnya
- Keduanya wajib ada. Satu tidak menggantikan yang lain.
- Livewire method bisa dipanggil langsung lewat HTTP POST — anggap seperti controller method.

---

## Urutan Perbaikan yang Disarankan

```
Bug 3 (nama acak)   → Bug 5 (satuan max) → Bug 4 (mimes)
     ↓
Bug 1 (disk privat) → Bug 2 (Gate download) → Bug 7 (Livewire)
     ↓
Bug 6 (berkas yatim)
```

Mulai dari Bug 3 dan 5 karena tidak memerlukan perubahan arsitektur — hanya
mengganti satu fungsi atau satu angka. Bug 1 dan 2 saling terkait (disk privat
baru berguna jika endpoint download juga diamankan). Bug 7 adalah pelajaran
pengulangan dari Bug 2 dalam konteks Livewire.

---

## Checklist Verifikasi Setelah Perbaikan

```bash
# Bug 1 — disk privat berhasil:
# URL storage/submissions/xyz.pdf harus 404 setelah storage:link
curl.exe -s -o NUL -w "%{http_code}\n" http://127.0.0.1:8000/storage/submissions/test.pdf
# Harapan: 404

# Bug 2 — IDOR tertutup:
# Akses submission orang lain harus 403
curl.exe -s -o NUL -w "%{http_code}\n" ^
  http://127.0.0.1:8000/mahasiswa/submissions/99/download ^
  -b C:\tmp\mhs-a.txt
# Harapan: 403

# Bug 3 — nama acak:
php artisan tinker -e "
  $a = App\Models\Submission::where('user_id',1)->first();
  $b = App\Models\Submission::where('user_id',2)->first();
  echo ($a->file_path === $b->file_path) ? 'SAMA - bug!' : 'BERBEDA - benar';
"
# Harapan: BERBEDA - benar

# Bug 5 — satuan benar:
# Upload file 1 MB harus berhasil jika max:5120
# Upload file 6 MB harus ditolak 422

# Bug 6 — berkas yatim tidak terjadi:
php artisan tinker -e "App\Models\Submission::find(1)->delete()"
# Cek: berkas fisik harus sudah hilang dari storage/app/submissions/
```

---

*Dokumen ini mendampingi `PANDUAN_W09.md` (panduan mengajar dan demo untuk setiap bug).*
