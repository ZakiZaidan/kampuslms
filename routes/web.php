<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// BUG 1: Route ini berada DI LUAR grup auth.
// Siapa pun — termasuk tamu yang belum login — bisa mengakses halaman profil.
// Seharusnya ada di dalam Route::middleware('auth')->group(...)
Route::get('/profile', function () {
    return view('profile');
})->name('profile');

Route::middleware('auth')->group(function () {

    // BUG 5a: nama route bentrok — dua grup berbeda sama-sama punya 'courses.index'.
    // Akibatnya: route('courses.index') akan selalu mengarah ke salah satu saja.
    // Seharusnya pakai name prefix: ->name('admin.') dan ->name('dosen.')
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('courses', CourseController::class);

        // BUG 6: Route destruktif (hapus semua submission) memakai GET.
        // GET seharusnya hanya untuk membaca data, tidak mengubah atau menghapus.
        // Browser bisa mengakses ini lewat preload, bot, atau link preview.
        // Seharusnya memakai method DELETE dan form dengan @method('DELETE').
        Route::get('/submissions/destroy-all', function () {
            \App\Models\Submission::truncate();
            return redirect()->back()->with('success', 'Semua submission dihapus.');
        })->name('submissions.destroy-all');
    });

    Route::middleware('role:dosen')->prefix('dosen')->group(function () {
        // BUG 5b: nama route bentrok dengan grup admin di atas.
        Route::resource('courses', CourseController::class);

        // BUG 3: nested route tanpa scopeBindings().
        // /dosen/courses/1/materials/99 akan berhasil meski material 99
        // sebenarnya milik courses lain — Laravel tidak memeriksa kepemilikan.
        Route::resource('courses.materials', MaterialController::class)->shallow();
    });

    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->group(function () {
        Route::resource('courses', CourseController::class)->only(['index', 'show']);
        Route::resource('submissions', SubmissionController::class)->only(['index', 'show', 'create', 'store']);
    });

});
