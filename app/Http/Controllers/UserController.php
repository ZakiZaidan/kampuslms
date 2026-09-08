<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    /**
     * BUG 6: Memakai $request->all() untuk mengisi model User.
     *
     * Karena User::$guarded = [] (Bug 5), SEMUA field dari request
     * diteruskan langsung ke database — termasuk field yang tidak ada
     * di form HTML, seperti 'role'.
     *
     * Penyerang cukup mengirim: POST /users dengan tambahan role=admin
     * → ia menjadi admin tanpa ada yang menghentikannya.
     *
     * Perbaikan yang benar:
     *   User::create($request->only(['name', 'email', 'password']));
     *   // lalu set role secara eksplisit jika diperlukan:
     *   // $user->role = 'student'; $user->save();
     */
    public function store(Request $request)
    {
        // BUG 6: $request->all() → semua input pengguna masuk, termasuk 'role'
        User::create($request->all());

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        // BUG 6 (juga di sini): $request->all() bisa mengubah role via request
        $user->update($request->all());

        return redirect()->route('users.show', $user)
                         ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }
}
