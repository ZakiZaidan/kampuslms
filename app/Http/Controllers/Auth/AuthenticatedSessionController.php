<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        // BUG 6: User enumeration — dua pesan berbeda memberi tahu penyerang
        // apakah email itu terdaftar atau tidak.
        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Akun dengan email ini tidak ditemukan.'],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kata sandi yang Anda masukkan tidak tepat.'],
            ]);
        }

        Auth::login($user);

        // BUG 5: session()->regenerate() DIHILANGKAN.
        // Tanpanya, ID session tidak berubah setelah login.
        // Penyerang yang berhasil menanamkan ID session tertentu pada korban
        // akan ikut "login" saat korban berhasil autentikasi.
        // Seharusnya: $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
