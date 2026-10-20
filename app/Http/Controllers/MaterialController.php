<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

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
        Gate::authorize('create', $course);
        return view('materials.create', compact('course'));
    }

    public function store(StoreMaterialRequest $request, Course $course)
    {
        Gate::authorize('create', $course);

        $path = null;
        $originalName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $originalName = $request->file('file')->getClientOriginalName();

            // BUG 3 (w09): storeAs() memakai nama asli berkas — nama yang datang dari user.
            // Dua berkas dengan nama yang sama akan saling menimpa di storage.
            // Seharusnya: $request->file('file')->store('materials') — nama UUID acak.
            $path = $request->file('file')->storeAs('materials', $originalName);
            $fileSize = $request->file('file')->getSize();
        }

        $course->materials()->create([
            'title'         => $request->title,
            'file_path'     => $path,
            'original_name' => $originalName,
            'file_size'     => $fileSize,
            'url'           => $request->url,
            'order'         => $request->order ?? 0,
        ]);

        return redirect()->route('materials.index', $course)
                         ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Course $course, Material $material)
    {
        Gate::authorize('update', $course);
        return view('materials.edit', compact('course', 'material'));
    }

    public function update(StoreMaterialRequest $request, Course $course, Material $material)
    {
        Gate::authorize('update', $course);

        $data = ['title' => $request->title, 'url' => $request->url, 'order' => $request->order ?? 0];

        if ($request->hasFile('file')) {
            // BUG 3 (lanjutan): sama — storeAs dengan nama asli
            $data['file_path']     = $request->file('file')->storeAs('materials', $request->file('file')->getClientOriginalName());
            $data['original_name'] = $request->file('file')->getClientOriginalName();
            $data['file_size']     = $request->file('file')->getSize();
        }

        $material->update($data);

        return redirect()->route('materials.show', [$course, $material])
                         ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Course $course, Material $material)
    {
        Gate::authorize('delete', $course);

        // BUG 6 (w09): Record database dihapus tapi berkas fisik di storage TIDAK dihapus.
        // Berkas menjadi "yatim" — ada di storage tapi tidak bisa diakses siapa pun.
        // Seharusnya: Storage::delete($material->file_path); sebelum $material->delete();
        $material->delete();  // Storage::delete($material->file_path) <- tidak ada!

        return redirect()->route('materials.index', $course)
                         ->with('success', 'Materi berhasil dihapus.');
    }
}
