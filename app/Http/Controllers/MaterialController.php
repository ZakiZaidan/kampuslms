<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaterialRequest;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Course $course): View
    {
        Gate::authorize('view', $course);

        $materials = $course->materials()->orderBy('order')->get();
        return view('courses.show', compact('course', 'materials'));
    }

    public function show(Material $material): View
    {
        Gate::authorize('view', $material);
        $course = $material->course;

        return view('materials.show', compact('course', 'material'));
    }

    public function create(Course $course): View
    {
        Gate::authorize('update', $course);

        return view('materials.create', compact('course'));
    }

    public function store(StoreMaterialRequest $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $path = null;
        $originalName = null;
        $fileSize = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileSize     = $file->getSize();
            // Disimpan dengan nama acak UUID ke disk privat local
            $path         = $file->store('materials', 'local');
        }

        $course->materials()->create([
            'title'         => $request->input('title'),
            'content'       => $request->input('content'),
            'file_path'     => $path,
            'original_name' => $originalName,
            'file_size'     => $fileSize,
            'url'           => $request->input('url'),
            'order'         => $request->input('order', 0),
        ]);

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Materi berhasil ditambahkan.');
    }

    public function edit(Material $material): View
    {
        $course = $material->course;
        Gate::authorize('update', $course);

        return view('materials.edit', compact('course', 'material'));
    }

    public function update(StoreMaterialRequest $request, Material $material): RedirectResponse
    {
        $course = $material->course;
        Gate::authorize('update', $course);

        $data = [
            'title'   => $request->input('title'),
            'content' => $request->input('content'),
            'url'     => $request->input('url'),
            'order'   => $request->input('order', 0),
        ];

        if ($request->hasFile('file')) {
            // Hapus berkas fisik lama agar tidak menjadi berkas yatim
            if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
                Storage::disk('local')->delete($material->file_path);
            }

            $file = $request->file('file');
            $data['file_path']     = $file->store('materials', 'local');
            $data['original_name'] = $file->getClientOriginalName();
            $data['file_size']     = $file->getSize();
        }

        $material->update($data);

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $course = $material->course;
        Gate::authorize('update', $course);

        // Hapus berkas fisik sebelum menghapus record dari database
        if ($material->file_path && Storage::disk('local')->exists($material->file_path)) {
            Storage::disk('local')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Materi berhasil dihapus.');
    }

    public function download(Material $material): StreamedResponse
    {
        Gate::authorize('view', $material);

        abort_unless($material->file_path && Storage::disk('local')->exists($material->file_path), 404, 'Berkas materi tidak ditemukan.');

        return Storage::disk('local')->download($material->file_path, $material->original_name);
    }
}
