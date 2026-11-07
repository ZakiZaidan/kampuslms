<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // Mencegah mass-assignment attack (misal eskalasi role) dengan hanya memperbarui data yang telah divalidasi
        $user->update($validated);

        return redirect()->route('profile.edit')
                         ->with('success', 'Profil berhasil diperbarui.');
    }
}
