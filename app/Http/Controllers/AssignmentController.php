<?php

namespace App\Http\Controllers;

use App\Events\AssignmentCreated;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function create(Course $course): View
    {
        Gate::authorize('update', $course);

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_at'      => ['nullable', 'date'],
        ]);

        $assignment = $course->assignments()->create($validated);

        // Memicu event pembuatan tugas:
        // Listener akan mengirimkan notifikasi ke seluruh mahasiswa terdaftar.
        event(new AssignmentCreated($assignment));

        return redirect()->route('dosen.courses.show', $course)
                         ->with('success', 'Tugas baru berhasil dipublikasikan.');
    }

    public function show(Course $course, Assignment $assignment): View
    {
        $assignment->load(['submissions.user', 'submissions.grade']);

        return view('assignments.show', compact('course', 'assignment'));
    }
}
