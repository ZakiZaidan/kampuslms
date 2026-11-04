<?php

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes();

// Otorisasi channel privat courses.{courseId}
// Hanya dosen pengampu atau mahasiswa yang terdaftar di kelas yang diizinkan subscribe.
Broadcast::channel('courses.{courseId}', function (User $user, int $courseId) {
    $course = Course::find($courseId);

    if (! $course) {
        return false;
    }

    return $course->lecturer_id === $user->id
        || $course->students()->where('user_id', $user->id)->exists();
});

// Otorisasi notifikasi pengguna privat
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === (int) $id;
});
