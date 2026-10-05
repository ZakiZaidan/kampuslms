<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    // BUG 4: Mass assignment — role bisa diubah lewat form update profil.
    // $request->all() menyertakan semua field yang dikirim, termasuk 'role'.
    // Pengguna bisa mengirim: name=A&email=a@a.com&role=admin
    // dan kolom role akan terupdate karena User memakai $guarded = [] (dari w03).
    // Seharusnya pakai $request->only(['name', 'email']) atau
    // Form Request dengan $fillable yang ketat.
    public function update(Request $request)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        auth()->user()->update($request->all());

        return redirect()->route('profile.edit')
                         ->with('success', 'Profil berhasil diperbarui.');
    }
}
