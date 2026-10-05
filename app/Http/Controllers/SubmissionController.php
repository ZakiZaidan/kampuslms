<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        $submissions = auth()->user()->submissions()->with('assignment')->latest()->get();
        return view('submissions.index', compact('submissions'));
    }

    // BUG 1b: Tidak ada Gate::authorize() — hanya @can di Blade yang menyembunyikan tombol.
    // Mahasiswa A bisa mengakses /submissions/{id} milik mahasiswa B
    // cukup dengan mengetik URL-nya langsung di browser.
    // Ini adalah IDOR dari minggu 5 yang belum ditutup oleh Policy.
    public function show(Submission $submission)
    {
        return view('submissions.show', compact('submission'));
    }

    public function create()
    {
        return view('submissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'assignment_id' => ['required', 'exists:assignments,id'],
            'note'          => ['nullable', 'string'],
        ]);

        $submission = Submission::create([
            'assignment_id' => $request->assignment_id,
            'user_id'       => auth()->id(),
            'note'          => $request->note,
            'submitted_at'  => now(),
        ]);

        return redirect()->route('submissions.show', $submission)
                         ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}
