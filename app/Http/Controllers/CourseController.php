<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    // Data dummy sebagai pengganti database
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
            'nama'      => 'Algoritma & Struktur Data',
            'sks'       => 4,
            'deskripsi' => 'Array, linked list, tree, graph, sorting, searching.',
        ],
    ];

    /** Daftar semua mata kuliah */
    public function index()
    {
        return view('courses.index', ['courses' => $this->courses]);
    }

    /** Halaman tambah mata kuliah */
    public function create()
    {
        return view('courses.create');
    }

    /** Detail satu mata kuliah berdasarkan ID */
    public function show(int $course)
    {
        // Cari course berdasarkan ID
        $found = collect($this->courses)->firstWhere('id', $course);

        if (! $found) {
            abort(404, 'Mata kuliah tidak ditemukan.');
        }

        return view('courses.show', ['course' => $found]);
    }
}
