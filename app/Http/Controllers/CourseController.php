<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        // BUG 3: filter disimpan ke session, bukan diambil dari query string.
        // Akibatnya: dua tab browser saling mengganggu, URL tidak bisa dibagikan,
        // dan tombol Back tidak bekerja sesuai harapan.
        if ($request->filled('q')) {
            session(['course_search' => $request->q]);
        }
        if ($request->filled('status')) {
            session(['course_status' => $request->status]);
        }

        $q      = session('course_search');
        $status = session('course_status');

        $courses = Course::query()
            ->with('lecturer')
            ->when($q,      fn ($query) => $query->where('name', 'like', "%$q%")
                                                  ->orWhere('code', 'like', "%$q%"))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            // BUG 4: ->withQueryString() dihilangkan.
            // Akibatnya: klik halaman 2 → filter hilang → pengguna bingung.
            ->paginate(15);

        return view('courses.index', compact('courses', 'q', 'status'));
    }

    public function create()
    {
        $lecturers = User::where('role', 'lecturer')->get();
        return view('courses.create', compact('lecturers'));
    }

    public function store(StoreCourseRequest $request)
    {
        Course::create($request->validated());

        // BUG 5: tidak redirect setelah store — langsung return view.
        // Akibatnya: tekan F5 setelah simpan → browser kirim ulang POST → data duplikat.
        return view('courses.index', [
            'courses' => Course::with('lecturer')->latest()->paginate(15),
            'q'       => null,
            'status'  => null,
        ]);
    }

    public function show(Course $course)
    {
        $course->load('lecturer', 'assignments', 'materials');
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $lecturers = User::where('role', 'lecturer')->get();
        return view('courses.edit', compact('course', 'lecturers'));
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());

        return redirect()->route('courses.show', $course)
                         ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')
                         ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
