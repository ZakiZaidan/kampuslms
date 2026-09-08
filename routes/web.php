<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;

Route::get('/', function () {
    return view('welcome');
});

// BUG 1: Route /courses/{course} dipasang SEBELUM /courses/create
//         → Saat user buka /courses/create, Laravel mencocokkan
//           {course} = "create" terlebih dahulu, bukan halaman create.
//
// BUG 2: Method HTTP untuk daftar mata kuliah diubah ke POST,
//         padahal browser selalu mengirim GET saat mengetik URL.
//         → Hasil: HTTP 405 Method Not Allowed.

Route::post('/courses',           [CourseController::class, 'index'])->name('courses.index');  // BUG 2: POST bukan GET
Route::get('/courses/{course}',   [CourseController::class, 'show'])->name('courses.show');    // BUG 1: di atas /create
Route::get('/courses/create',     [CourseController::class, 'create'])->name('courses.create'); // tertutup oleh {course}
