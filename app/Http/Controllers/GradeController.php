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

        event(new SubmissionGraded($submission, $grade));

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    public function courseGrades(Course $course): View
    {
        Gate::authorize('view', $course);

        $appName      = config('app.name', 'KampusLMS');
        $academicYear = config('app.academic_year', '2026/2027');

        // Eager loading efisien 3 tingkat mencegah N+1 query
        $course->load([
            'assignments' => fn ($q) => $q->select(['id', 'course_id', 'title', 'due_at']),
            'assignments.submissions' => fn ($q) => $q->select(['id', 'assignment_id', 'user_id', 'submitted_at', 'is_late', 'file_path', 'original_name']),
            'assignments.submissions.user:id,name,email',
            'assignments.submissions.grade:id,submission_id,score,feedback',
        ]);

        // Agregasi SQL langsung tanpa memuat seluruh model ke memori
        $totalStudents = $course->students()->count();

        return view('grades.course', compact('course', 'totalStudents', 'appName', 'academicYear'));
    }

    public function studentGrades(): View
    {
        // Pemuatan data aman menggunakan pagination
        $submissions = auth()->user()->submissions()
            ->with(['assignment.course:id,code,name', 'grade:id,submission_id,score,feedback'])
            ->latest('submitted_at')
            ->paginate(10);

        return view('grades.student', compact('submissions'));
    }
}
