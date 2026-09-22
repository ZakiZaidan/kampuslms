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

    // BUG 2b: IDOR — tidak ada pengecekan bahwa material ini milik course ini.
    // Tanpa scopeBindings() di route, URL /dosen/courses/1/materials/99
    // akan berhasil meski material 99 milik courses lain.
    // Selain itu, tidak ada pengecekan bahwa dosen yang mengakses
    // adalah pengampu courses ini.
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
