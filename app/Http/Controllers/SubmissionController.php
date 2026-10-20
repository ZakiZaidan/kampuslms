<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = auth()->user()->submissions()->with('assignment')->latest()->get();
        return view('submissions.index', compact('submissions'));
    }

    // BUG 2 (w09): Tidak ada Gate::authorize() — IDOR versi berkas.
    // Mahasiswa A bisa mengakses /submissions/{id} milik Mahasiswa B.
    public function show(Submission $submission)
    {
        return view('submissions.show', compact('submission'));
    }

    public function store(StoreSubmissionRequest $request, Assignment $assignment)
    {
        // BUG 1 (w09): Disimpan ke disk 'public' — berkas bisa diakses tanpa login
        // lewat URL: /storage/submissions/nama-berkas.pdf
        // Seharusnya: $request->file('file')->store('submissions') — disk local (privat)
        $originalName = $request->file('file')->getClientOriginalName();

        // BUG 3 (w09): storeAs() memakai nama asli berkas.
        // Dua mahasiswa dengan berkas bernama sama akan saling menimpa.
        // Path traversal juga dimungkinkan jika nama berkas mengandung '../'.
        // Seharusnya: $request->file('file')->store('submissions') untuk nama acak UUID.
        $path = $request->file('file')->storeAs('submissions', $originalName, 'public');

        // BUG 7 (w09): is_late diterima dari request (bisa dimanipulasi client).
        // Seharusnya dihitung di server: now()->isAfter($assignment->deadline)
        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
            [
                'file_path'     => $path,
                'original_name' => $originalName,
                'file_size'     => $request->file('file')->getSize(),
                'is_late'       => $request->boolean('is_late'),  // BUG 7 — dari client!
                'submitted_at'  => now(),
            ]
        );

        return redirect()->route('submissions.index')
                         ->with('success', 'Tugas berhasil dikumpulkan.');
    }

    // BUG 2 (w09): Endpoint download tanpa Gate::authorize().
    // Mahasiswa A bisa mengunduh berkas submission milik Mahasiswa B
    // cukup dengan mengetik: /submissions/99/download
    public function download(Submission $submission): StreamedResponse
    {
        // Gate::authorize('view', $submission);  // <- seharusnya ada di sini!
        abort_unless(Storage::disk('public')->exists($submission->file_path), 404);
        return Storage::disk('public')->download($submission->file_path, $submission->original_name);
    }
}
