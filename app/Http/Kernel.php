<?php

namespace App\Http;

// BUG 4: File ini TIDAK ADA di Laravel 12.
//
// Middleware di Laravel 12 didaftarkan di bootstrap/app.php, bukan di sini.
// File Kernel.php hanya ada di Laravel 10 ke bawah.
//
// Siapa pun yang mendaftarkan alias middleware di sini (mengikuti tutorial lama)
// akan mendapati alias 'role' tidak dikenali → error saat route diakses:
//   "Target class [role] does not exist."
//
// Perbaikan: hapus file ini, daftarkan alias di bootstrap/app.php.

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // ← alias yang didaftarkan di sini tidak akan bekerja di Laravel 12
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ];
}
