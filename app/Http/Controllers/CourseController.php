<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Data dummy — BUG 5: data ke-3 menyuntikkan XSS payload pada field 'nama'.
    // Karena view memakai {!! !!}, script ini AKAN dieksekusi oleh browser.
    private array $courses = [
        [
            'id'        => 1,
            'kode'      => 'PWB101',
            'nama'      => 'Pemrograman Web',
            'sks'       => 3,
            'deskripsi' => 'Belajar HTML, CSS, JavaScript, dan framework web modern.',
        ],
        [
            'id'        => 2,
            'kode'      => 'BDT201',
            'nama'      => 'Basis Data',
            'sks'       => 3,
            'deskripsi' => 'Relational database, SQL, dan normalisasi data.',
        ],
        [
            'id'        => 3,
            'kode'      => 'ALG301',
            // BUG 5: nilai 'nama' berisi XSS payload.
            // Karena show.blade.php memakai {!! $course['nama'] !!},
            // browser akan mengeksekusi alert ini.
            'nama'      => "Algoritma & Struktur Data <script>alert('⚠️ XSS! Data dari DB bisa mencuri sesi Anda!')</script>",
            'sks'       => 4,
            'deskripsi' => 'Array, linked list, tree, graph, sorting, searching.',
        ],
    ];

    /** Daftar semua mata kuliah — controller BERSIH, hanya kirim data */
    public function index()
    {
        return view('courses.index', ['courses' => $this->courses]);
    }

    /** Halaman tambah mata kuliah */
    public function create()
    {
        return view('courses.create');
    }

    /**
     * Detail satu mata kuliah.
     *
     * BUG 6: Logika pencarian (filter/query) SEHARUSNYA ada di sini,
     * di dalam controller. Pada versi buggy ini, controller hanya
     * meneruskan SEMUA data ke view, lalu view yang melakukan filter.
     * Lihat courses/show.blade.php untuk melihat logika yang salah tempat.
     */
    public function show(int $course)
    {
        // BUG 6: Tidak ada pencarian di sini. Semua $courses dikirim ke view,
        // dan view yang memfilter sendiri — melanggar prinsip MVC.
        return view('courses.show', [
            'courses'   => $this->courses,  // kirim semua, biar view yang pilih
            'courseId'  => $course,
        ]);
    }
}
