<?php

namespace App\Http\Controllers;

use App\Events\SubmissionGraded;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GradeController extends Controller
{
    // Dosen memberikan nilai + feedback pada submission:
    public function store(Request $request, Submission $submission): RedirectResponse
    {
        $course = $submission->assignment->course;
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'score'    => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $grade = Grade::updateOrCreate(
            ['submission_id' => $submission->id],
            [
                'score'    => $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
            ]
        );

        // Memicu event penilaian tugas:
        // Listener akan mengirimkan notifikasi ke mahasiswa terkait.
        event(new SubmissionGraded($submission, $grade));

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    // Rekap nilai per mata kuliah untuk Dosen:
    public function courseGrades(Course $course): View
    {
        Gate::authorize('view', $course);

        $course->load([
            'students',
            'assignments.submissions' => function ($q) {
                $q->with('grade');
            },
        ]);

        return view('grades.course', compact('course'));
    }

    // Rekap nilai untuk Mahasiswa:
    public function studentGrades(): View
    {
        $submissions = auth()->user()->submissions()
            ->with(['assignment.course', 'grade'])
            ->latest('submitted_at')
            ->get();

        return view('grades.student', compact('submissions'));
    }
}
