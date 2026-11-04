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

        event(new SubmissionGraded($submission, $grade));

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }

    // Rekap nilai per mata kuliah untuk Dosen:
    public function courseGrades(Course $course): View
    {
        Gate::authorize('view', $course);

        // BUG 6 (w11): Memanggil env() langsung di controller!
        // Saat 'php artisan config:cache' dijalankan di server produksi,
        // seluruh pemanggilan env() di luar config/ akan mengembalikan NULL!
        // Seharusnya: config('app.name') dan config('app.academic_year', '2026/2027')
        $appName      = env('APP_NAME'); // BUG 6: null saat config:cache!
        $academicYear = env('ACADEMIC_YEAR', '2026/2027');

        // BUG 2 (w11): Memuat seluruh koleksi Model User ke memori PHP hanya untuk menghitung jumlah!
        // count($course->students) memicu SELECT * FROM users dan mengalokasikan ratusan objek User di RAM.
        // Seharusnya: $totalStudents = $course->students()->count(); atau $course->loadCount('students');
        $totalStudents = count($course->students); // BUG 2: Boros RAM!

        // BUG 1 (w11): N+1 Query pada tiga tingkat relasi!
        // $course TIDAK di-eager-load relasi cucu/cicitnya.
        // Di view:
        //   $assignment->submissions         -> N query per tugas
        //   $submission->user->name          -> N query per submission
        //   $submission->grade->score        -> N query per submission
        // Menghasilkan 80-150+ query database untuk 1 halaman!
        //
        // BUG 3 (w11): SELECT * tanpa proyeksi kolom.
        // Seluruh kolom teks panjang (description) ditarik padahal hanya butuh ID dan judul.
        //
        // Solusi BUG 1 & 3:
        // $course->load([
        //     'assignments' => fn($q) => $q->select(['id', 'course_id', 'title', 'due_at']),
        //     'assignments.submissions' => fn($q) => $q->select(['id', 'assignment_id', 'user_id', 'submitted_at', 'is_late']),
        //     'assignments.submissions.user:id,name,email',
        //     'assignments.submissions.grade:id,submission_id,score,feedback',
        // ]);
        // $course->loadCount('students');

        return view('grades.course', compact('course', 'totalStudents', 'appName', 'academicYear'));
    }

    // Rekap nilai untuk Mahasiswa:
    public function studentGrades(): View
    {
        // BUG 5 (w11): Pemuatan data tanpa pagination!
        // Memuat seluruh data pengumpulan sekaligus dengan ->get().
        // Jika data membesar hingga ratusan tugas, berisiko Out Of Memory (OOM).
        //
        // Solusi:
        // $submissions = auth()->user()->submissions()
        //     ->with(['assignment.course:id,code,name', 'grade:id,submission_id,score'])
        //     ->latest('submitted_at')
        //     ->paginate(15);
        $submissions = auth()->user()->submissions()
            ->with(['assignment.course', 'grade'])
            ->latest('submitted_at')
            ->get(); // BUG 5: Unpaginated!

        return view('grades.student', compact('submissions'));
    }
}
