<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->with('lecturer')
            ->withCount(['materials', 'assignments'])
            ->paginate(15);

        return CourseResource::collection($courses);
    }

    public function store(StoreCourseRequest $request)
    {
        $course = Course::create($request->validated());

        $course->load('lecturer')->loadCount(['materials', 'assignments']);

        return (new CourseResource($course))->response()->setStatusCode(201);
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

    public function destroy(Course $course)
    {
        $course->delete();

        return response()->noContent();
    }
}
