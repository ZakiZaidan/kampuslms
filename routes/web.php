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

Route::get('/', fn () => view('welcome'))->name('welcome');

// Auth routes
Route::get('/login',  [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout',[AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::get('/profile',   [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile',   [ProfileController::class, 'update'])->name('profile.update');

    // Mata Kuliah & Tugas (Umum untuk semua user terotentikasi, diotorisasi via Policy)
    Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');

    // Tugas & Berkas (Download & View)
    Route::get('/assignments/{assignment}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');
    Route::get('/submissions/{submission}/download', [SubmissionController::class, 'download'])->name('submissions.download');

    // Notifikasi (semua pengguna yang login)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');

    // Khusus Mahasiswa
    Route::middleware('role:mahasiswa')->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/submissions/{submission}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
        Route::get('/grades', [GradeController::class, 'studentGrades'])->name('grades.index');
    });

    // Khusus Dosen
    Route::middleware('role:dosen')->prefix('dosen')->name('dosen.')->group(function () {
        Route::resource('courses.materials', MaterialController::class)->shallow();
        Route::resource('courses.assignments', AssignmentController::class)->only(['create', 'store', 'destroy'])->shallow();
        Route::post('/submissions/{submission}/grade', [GradeController::class, 'store'])->name('submissions.grade');
        Route::get('/courses/{course}/grades', [GradeController::class, 'courseGrades'])->name('courses.grades');
    });

    // Khusus Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('courses', CourseController::class);
    });

});
