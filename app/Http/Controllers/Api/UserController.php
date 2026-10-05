<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // BUG 1b: Model mentah dikembalikan langsung — seluruh kolom bocor ke klien.
    // Termasuk: password (hash bcrypt), remember_token, email_verified_at.
    // Seharusnya memakai UserResource yang sudah di-whitelist field-nya.
    public function index()
    {
        return response()->json(User::all());
    }

    public function show(User $user)
    {
        // BUG 5: Status code 403 dikembalikan sebagai 401.
        // Ini terjadi kalau mahasiswa mencoba lihat detail user lain.
        // 401 = "belum login" | 403 = "sudah login tapi tidak berhak"
        // Memberikan 401 pada pengguna yang sudah login menyebabkan
        // klien (app mobile) mencoba login ulang — padahal token valid.
        if (auth()->user()->role !== 'admin' && auth()->id() !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return new UserResource($user);
    }
}
