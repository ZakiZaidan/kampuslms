<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index()
    {
        // Hanya tampilkan submission milik user yang login
        $submissions = auth()->user()->submissions()->with('assignment')->latest()->get();
        return view('submissions.index', compact('submissions'));
    }

    // BUG 2a: IDOR — tidak ada pengecekan kepemilikan.
    // Siapa pun yang login bisa mengakses /submissions/{id} milik orang lain
    // hanya dengan mengganti angka di URL.
    // Route model binding hanya menjamin data ADA, bukan bahwa pengguna BERHAK melihatnya.
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
