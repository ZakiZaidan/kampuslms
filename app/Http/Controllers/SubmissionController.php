<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubmissionRequest;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function index(): View
    {
        $submissions = auth()->user()->submissions()
            ->with(['assignment.course', 'grade'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('grades.student', compact('submissions'));
    }

    public function show(Submission $submission): View
    {
        Gate::authorize('view', $submission);

        $submission->load(['assignment.course', 'user', 'grade']);

        return view('assignments.show', [
            'course'     => $submission->assignment->course,
            'assignment' => $submission->assignment,
            'submission' => $submission,
        ]);
    }

    public function store(StoreSubmissionRequest $request, Assignment $assignment): RedirectResponse
    {
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSize     = $file->getSize();

        // Disimpan ke disk privat 'local' dengan nama acak UUID (aman dari overwriting & path traversal)
        $path = $file->store('submissions', 'local');

        // is_late dihitung di server berdasarkan tenggat tugas
        $isLate = $assignment->due_at ? now()->isAfter($assignment->due_at) : false;

        // updateOrCreate agar mahasiswa bisa merevisi tugas tanpa duplikasi data
        Submission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'user_id' => auth()->id()],
            [
                'file_path'     => $path,
                'original_name' => $originalName,
                'file_size'     => $fileSize,
                'is_late'       => $isLate,
                'note'          => $request->input('note'),
                'submitted_at'  => now(),
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function download(Submission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);

        abort_unless(Storage::disk('local')->exists($submission->file_path), 404, 'Berkas tidak ditemukan.');

        return Storage::disk('local')->download($submission->file_path, $submission->original_name);
    }
}
