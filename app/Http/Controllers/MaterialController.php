<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Course $course)
    {
        $materials = $course->materials()->orderBy('order')->get();
        return view('materials.index', compact('course', 'materials'));
    }

    public function show(Course $course, Material $material)
    {
        return view('materials.show', compact('course', 'material'));
    }

    public function create(Course $course)
    {
        return view('materials.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'order'   => ['nullable', 'integer'],
        ]);

        // BUG 9: $this->authorize() dipakai bukan Gate::authorize().
        // Di Laravel 11+, kelas dasar Controller sudah TIDAK menyertakan
        // trait AuthorizesRequests lagi, sehingga $this->authorize() akan
        // melempar: "Call to undefined method authorize()".
        // Seharusnya: Gate::authorize('create', $course);
        $this->authorize('create', $course);

        $material = $course->materials()->create($request->only(['title', 'content', 'order']));

        return redirect()->route('materials.show', [$course, $material])
                         ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Course $course, Material $material)
    {
        return view('materials.edit', compact('course', 'material'));
    }

    public function update(Request $request, Course $course, Material $material)
    {
        $request->validate([
            'title'   => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'order'   => ['nullable', 'integer'],
        ]);

        // BUG 9 (lanjutan): $this->authorize() juga di sini.
        $this->authorize('update', $course);

        $material->update($request->only(['title', 'content', 'order']));

        return redirect()->route('materials.show', [$course, $material])
                         ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Course $course, Material $material)
    {
        $material->delete();
        return redirect()->route('materials.index', $course)
                         ->with('success', 'Materi berhasil dihapus.');
    }
}
