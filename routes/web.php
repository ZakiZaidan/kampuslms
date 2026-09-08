<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});

// Route mata kuliah (benar di w03 — bug ada di migrasi & model)
Route::get('/courses',           [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/create',    [CourseController::class, 'create'])->name('courses.create');
Route::get('/courses/{course}',  [CourseController::class, 'show'])->name('courses.show');

// Route pengguna — UserController pakai $request->all() (Bug 6)
Route::resource('users', UserController::class);
