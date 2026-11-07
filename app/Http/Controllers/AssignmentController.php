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

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Tugas baru berhasil dipublikasikan.');
    }

    public function show(Assignment $assignment): View
    {
        $assignment->load(['course.lecturer', 'submissions.user', 'submissions.grade']);
        $course = $assignment->course;

        Gate::authorize('view', $course);

        $mySubmission = null;
        if (auth()->check() && in_array(auth()->user()->role, ['mahasiswa', 'student'])) {
            $mySubmission = $assignment->submissions()
                ->where('user_id', auth()->id())
                ->with('grade')
                ->first();
        }

        return view('assignments.show', compact('course', 'assignment', 'mySubmission'));
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $course = $assignment->course;
        Gate::authorize('update', $course);

        $assignment->delete();

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Tugas berhasil dihapus.');
    }
}
