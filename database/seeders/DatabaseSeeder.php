<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('password');

        // 1. Akun Pengguna Demo
        $admin = User::firstOrCreate(
            ['email' => 'admin@kampus.id'],
            [
                'name'     => 'Administrator Kampus',
                'password' => $defaultPassword,
                'role'     => 'admin',
            ]
        );

        $dosen1 = User::firstOrCreate(
            ['email' => 'dosen@kampus.id'],
            [
                'name'     => 'Dr. Ir. Budi Santoso, M.Kom',
                'password' => $defaultPassword,
                'role'     => 'dosen',
            ]
        );

        $dosen2 = User::firstOrCreate(
            ['email' => 'siti@kampus.id'],
            [
                'name'     => 'Siti Aminah, S.Kom, M.T',
                'password' => $defaultPassword,
                'role'     => 'dosen',
            ]
        );

        $mhs1 = User::firstOrCreate(
            ['email' => 'mahasiswa@kampus.id'],
            [
                'name'     => 'Ahmad Zaki (Demo Mahasiswa)',
                'password' => $defaultPassword,
                'role'     => 'mahasiswa',
            ]
        );

        $mhs2 = User::firstOrCreate(
            ['email' => 'budi.mhs@kampus.id'],
            [
                'name'     => 'Budi Pratama',
                'password' => $defaultPassword,
                'role'     => 'mahasiswa',
            ]
        );

        $mhs3 = User::firstOrCreate(
            ['email' => 'citra@kampus.id'],
            [
                'name'     => 'Citra Lestari',
                'password' => $defaultPassword,
                'role'     => 'mahasiswa',
            ]
        );

        // 2. Mata Kuliah Demo
        $course1 = Course::firstOrCreate(
            ['code' => 'IF201'],
            [
                'name'        => 'Pemrograman Web Lanjutan',
                'description' => 'Mempelajari arsitektur web modern, REST API, autentikasi, queue, dan deployment produksi dengan Laravel.',
                'sks'         => 3,
                'lecturer_id' => $dosen1->id,
                'status'      => 'published',
            ]
        );

        $course2 = Course::firstOrCreate(
            ['code' => 'IF305'],
            [
                'name'        => 'Rekayasa Perangkat Lunak',
                'description' => 'Prinsip desain sistem, agile methodology, version control, dan arsitektur pengujian perangkat lunak.',
                'sks'         => 3,
                'lecturer_id' => $dosen2->id,
                'status'      => 'published',
            ]
        );

        $course3 = Course::firstOrCreate(
            ['code' => 'IF102'],
            [
                'name'        => 'Basis Data Relasional',
                'description' => 'Model relasional, normalisasi skema, indexing, query optimization, dan transaksi database ACID.',
                'sks'         => 3,
                'lecturer_id' => $dosen1->id,
                'status'      => 'published',
            ]
        );

        // 3. Pendaftaran Mahasiswa ke Kelas (Enrollment)
        $students = [$mhs1->id, $mhs2->id, $mhs3->id];
        $course1->students()->syncWithoutDetaching($students);
        $course2->students()->syncWithoutDetaching([$mhs1->id, $mhs2->id]);
        $course3->students()->syncWithoutDetaching([$mhs1->id, $mhs3->id]);

        // 4. Materi Kuliah
        Material::firstOrCreate(
            ['course_id' => $course1->id, 'title' => 'Pertemuan 1 — Pengantar Arsitektur Laravel'],
            [
                'content' => 'Pengenalan siklus hidup request Laravel, routing, controller, dan konfigurasi lingkungan .env.',
                'url'     => 'https://laravel.com/docs',
                'order'   => 1,
            ]
        );

        Material::firstOrCreate(
            ['course_id' => $course1->id, 'title' => 'Pertemuan 2 — REST API & Authentication'],
            [
                'content' => 'Konsep stateless authentication dengan Laravel Sanctum, API Resource, dan validasi Form Request.',
                'url'     => 'https://laravel.com/docs/sanctum',
                'order'   => 2,
            ]
        );

        Material::firstOrCreate(
            ['course_id' => $course2->id, 'title' => 'Modul 1 — Git Workflow & Branching Strategy'],
            [
                'content' => 'Panduan alur kolaborasi tim menggunakan Git Flow dan Pull Request review.',
                'url'     => 'https://git-scm.com',
                'order'   => 1,
            ]
        );

        // 5. Tugas Kuliah (Assignments)
        $tugas1 = Assignment::firstOrCreate(
            ['course_id' => $course1->id, 'title' => 'Tugas 1 — Implementasi REST API KampusLMS'],
            [
                'description' => 'Bangun endpoint REST API untuk manajemen materi kuliah dengan response standar JSON dan autentikasi token Sanctum.',
                'due_at'      => now()->addDays(7),
            ]
        );

        $tugas2 = Assignment::firstOrCreate(
            ['course_id' => $course1->id, 'title' => 'Tugas 2 — Sistem Upload Berkas & Otorisasi Policy'],
            [
                'description' => 'Implementasikan upload berkas tugas pada disk privat dengan validasi MIME PDF dan proteksi Gate Policy.',
                'due_at'      => now()->addDays(14),
            ]
        );

        $tugasRpl = Assignment::firstOrCreate(
            ['course_id' => $course2->id, 'title' => 'Tugas 1 — Diagram Arsitektur & ERD'],
            [
                'description' => 'Rancang Entity Relationship Diagram (ERD) dan diagram alur sistem untuk modul LMS kampus.',
                'due_at'      => now()->subDays(2), // Deadline telah lewat
            ]
        );

        // 6. Pengumpulan Tugas (Submissions)
        $sub1 = Submission::firstOrCreate(
            ['assignment_id' => $tugas1->id, 'user_id' => $mhs1->id],
            [
                'file_path'     => 'submissions/demo_tugas1.pdf',
                'original_name' => 'Tugas1_AhmadZaki.pdf',
                'file_size'     => 1024 * 450, // 450 KB
                'is_late'       => false,
                'note'          => 'Tugas 1 sudah selesai dengan seluruh endpoint API teruji di Postman.',
                'submitted_at'  => now()->subDays(1),
            ]
        );

        $sub2 = Submission::firstOrCreate(
            ['assignment_id' => $tugas1->id, 'user_id' => $mhs2->id],
            [
                'file_path'     => 'submissions/demo_tugas1_budi.pdf',
                'original_name' => 'Tugas1_BudiPratama.pdf',
                'file_size'     => 1024 * 320,
                'is_late'       => false,
                'note'          => 'Laporan praktikum dan koleksi postman terlampir.',
                'submitted_at'  => now()->subHours(10),
            ]
        );

        $sub3 = Submission::firstOrCreate(
            ['assignment_id' => $tugasRpl->id, 'user_id' => $mhs1->id],
            [
                'file_path'     => 'submissions/demo_tugas_rpl.pdf',
                'original_name' => 'ERD_KampusLMS_Zaki.pdf',
                'file_size'     => 1024 * 600,
                'is_late'       => true,
                'note'          => 'Mohon maaf terlambat mengumpulkan karena kendala teknis jaringan.',
                'submitted_at'  => now()->subDay(),
            ]
        );

        // 7. Nilai & Feedback (Grades)
        Grade::updateOrCreate(
            ['submission_id' => $sub1->id],
            [
                'score'    => 95,
                'feedback' => 'Sangat bagus! Arsitektur API bersih, dokumentasi rapi, dan seluruh response code HTTP tepat.',
            ]
        );

        Grade::updateOrCreate(
            ['submission_id' => $sub3->id],
            [
                'score'    => 85,
                'feedback' => 'Analisis ERD sangat baik dan lengkap, ada sedikit pengurangan nilai karena keterlambatan pengumpulan.',
            ]
        );
    }
}
