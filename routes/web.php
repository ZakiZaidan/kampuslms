<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', fn () => view('welcome'));

// Auth routes
Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout',[AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',   [ProfileController::class, 'update'])->name('profile.update');

    // Notifikasi (semua pengguna yang login)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // BUG 4 (w10): Endpoint markAsRead rentan IDOR jika method controller tidak cek kepemilikan!
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('courses', CourseController::class);
    });

    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::resource('submissions', SubmissionController::class)
             ->only(['index', 'show']);

        // Route pengumpulan tugas — dipasangkan ke Assignment
        Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])
             ->name('submissions.store');

        // BUG 1 + BUG 2 (w09): Endpoint download ada, tapi berkas ada di disk 'public'
        // (bisa diakses langsung via /storage/...) dan method tidak ada Gate::authorize().
        Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])
             ->name('submissions.download');

        // Rekap nilai mahasiswa:
        Route::get('/grades', [GradeController::class, 'studentGrades'])->name('grades.index');
    });


    // BUG 7: Route mata kuliah untuk dosen berada DI LUAR grup middleware role:dosen.
    // Semua pengguna yang sudah login (termasuk mahasiswa) bisa mengakses
    // /dosen/courses, /dosen/courses/create, dst — tanpa pengecekan peran.
    Route::prefix('dosen')->name('dosen.')->group(function () {
        Route::resource('courses', CourseController::class)->except(['index', 'show']);
        Route::resource('courses.materials', MaterialController::class)->shallow();

        // Manajemen Tugas (Assignments) oleh Dosen:
        Route::resource('courses.assignments', AssignmentController::class)->shallow();

        // Penilaian (Grading) & Rekap Nilai oleh Dosen:
        Route::post('/submissions/{submission}/grade', [GradeController::class, 'store'])->name('submissions.grade');
        Route::get('/courses/{course}/grades', [GradeController::class, 'courseGrades'])->name('courses.grades');
    });

});
