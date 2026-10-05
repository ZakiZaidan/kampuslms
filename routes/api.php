<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\UserController;

// BUG 6: Endpoint login TIDAK dibatasi rate limiting.
// Tanpa throttle, penyerang bisa mencoba ribuan kata sandi per detik.
// Seharusnya: ->middleware('throttle:5,1')
Route::post('/v1/auth/login', [AuthController::class, 'login']);

// BUG 2: Endpoint ini berada di luar middleware auth:sanctum.
// Siapa pun bisa mengakses daftar users TANPA token.
// Akibatnya: nama, email, hash password semua pengguna bisa diakses publik.
Route::get('/v1/users', [UserController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/v1/auth/logout', [AuthController::class, 'logout']);
    Route::get('/v1/auth/me',     [AuthController::class, 'me']);

    Route::get('/v1/users/{user}', [UserController::class, 'show']);

    // Endpoint courses dan assignments terlindungi dengan benar
    Route::apiResource('v1/courses', CourseController::class);

});
