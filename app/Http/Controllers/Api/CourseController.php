<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseController extends Controller
{
    // BUG 8: N+1 query — tidak ada eager loading.
    // Setiap baris memanggil $course->lecturer secara terpisah
    // = 1 query untuk daftar + N query untuk setiap dosen.
    // Dengan 15 MK = 16 query. Seharusnya ->with('lecturer').
    public function index()
    {
        $courses = Course::paginate(15);
        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $course = Course::create($request->validated());

        // BUG 3: Status code salah — store harus mengembalikan 201 Created,
        // bukan 200 OK. 200 berarti "berhasil, tidak ada yang dibuat".
        // 201 berarti "berhasil, resource baru dibuat".
        return new CourseResource($course);
    }

    public function show(Course $course)
    {
        $course->load('lecturer');
        return new CourseResource($course);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->validated());
        return new CourseResource($course);
    }

    // BUG 4: Status code salah — destroy harus mengembalikan 204 No Content,
    // bukan 200 OK dengan body. 204 berarti "berhasil, tidak ada konten untuk dikembalikan".
    // Klien (mobile app, SPA) mengandalkan status code yang tepat untuk tahu
    // operasi berhasil tanpa perlu membaca body response.
    public function destroy(Course $course)
    {
        $course->delete();
        return response()->json(['message' => 'Mata kuliah berhasil dihapus.']);
    }
}
