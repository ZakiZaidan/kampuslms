<?php
// Script eksperimen BREAK — jalankan dengan: php artisan tinker < eksperimen/break_all.php

// ============================================================
// BREAK 1 — Unique Composite Hilang di course_user
// ============================================================
echo "\n=== BREAK 1: Unique Composite ===\n";

// Buat dosen dan mata kuliah dummy
$dosen = App\Models\User::firstOrCreate(
    ['email' => 'dosen@break.test'],
    ['name' => 'Pak Dosen', 'password' => 'password', 'role' => 'lecturer']
);

$mhs = App\Models\User::firstOrCreate(
    ['email' => 'mhs@break.test'],
    ['name' => 'Bu Mahasiswa', 'password' => 'password', 'role' => 'student']
);

$mk = App\Models\Course::firstOrCreate(
    ['code' => 'BREAK01'],
    ['name' => 'MK Eksperimen', 'sks' => 3, 'lecturer_id' => $dosen->id, 'status' => 'active']
);

// Hapus pendaftaran lama kalau ada
DB::table('course_user')->where('course_id', $mk->id)->where('user_id', $mhs->id)->delete();

// Coba daftar dua kali
DB::table('course_user')->insert(['course_id' => $mk->id, 'user_id' => $mhs->id, 'created_at' => now(), 'updated_at' => now()]);
DB::table('course_user')->insert(['course_id' => $mk->id, 'user_id' => $mhs->id, 'created_at' => now(), 'updated_at' => now()]);

$jumlah = DB::table('course_user')->where('course_id', $mk->id)->where('user_id', $mhs->id)->count();
echo "Mahasiswa yang sama terdaftar $jumlah kali di MK yang sama\n";
echo ($jumlah > 1) ? "❌ DUPLIKAT MASUK — tidak ada yang mencegah!\n" : "✅ Aman\n";

// ============================================================
// BREAK 2 — Mass Assignment: role=admin lewat create()
// ============================================================
echo "\n=== BREAK 2: Mass Assignment ===\n";

App\Models\User::where('email', 'penyusup@break.test')->delete();

// Ini yang dilakukan controller dengan $request->all()
App\Models\User::create([
    'name'     => 'Penyusup',
    'email'    => 'penyusup@break.test',
    'password' => 'rahasia123',
    'role'     => 'admin',   // field ini tidak ada di form pendaftaran
]);

$role = App\Models\User::where('email', 'penyusup@break.test')->value('role');
echo "Role yang berhasil disuntikkan: $role\n";
echo ($role === 'admin') ? "❌ MASS ASSIGNMENT BERHASIL — penyusup jadi admin!\n" : "✅ Aman\n";

// ============================================================
// BREAK 3 — Demonstrasi: kalau $fillable dipakai dengan benar
// ============================================================
echo "\n=== Perbandingan: dengan $fillable yang benar ===\n";
echo "Jika User punya: protected \$fillable = ['name','email','password']\n";
echo "→ 'role' tidak ada di fillable → kolom role diabaikan → user tetap 'student'\n";
echo "→ Inilah mengapa \$fillable whitelist lebih aman dari \$guarded blacklist\n";

// ============================================================
// BREAK 5 — cascade: hapus dosen, lihat MK ikut hilang
// ============================================================
echo "\n=== BREAK 5: cascadeOnDelete pada lecturer_id ===\n";

$jumlahMkSebelum = App\Models\Course::count();
echo "Jumlah MK sebelum hapus dosen: $jumlahMkSebelum\n";

// Karena courses.lecturer_id pakai cascadeOnDelete (Bug 3 di w03),
// menghapus dosen akan ikut menghapus semua MK-nya
$dosenHapus = App\Models\User::where('email', 'dosen@break.test')->first();
if ($dosenHapus) {
    $mkMilikDosen = App\Models\Course::where('lecturer_id', $dosenHapus->id)->count();
    echo "MK yang diampu dosen ini: $mkMilikDosen\n";
    $dosenHapus->delete();
    $jumlahMkSesudah = App\Models\Course::count();
    echo "Jumlah MK sesudah hapus dosen: $jumlahMkSesudah\n";
    echo ($jumlahMkSesudah < $jumlahMkSebelum)
        ? "❌ CASCADE — MK ikut terhapus! Kehilangan ".($jumlahMkSebelum-$jumlahMkSesudah)." MK\n"
        : "✅ Data aman (restrict bekerja)\n";
}

echo "\n=== Selesai. Jalankan migrate:fresh untuk reset database ===\n";
