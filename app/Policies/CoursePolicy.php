<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    // BUG 2: Seluruh Policy selalu mengembalikan true.
    // Policy ini ADA dan TERDAFTAR, tapi tidak melakukan pemeriksaan apa pun.
    // Dosen mana pun bisa mengedit/menghapus MK dosen lain,
    // mahasiswa bisa menghapus MK — tidak ada yang dicegah.
    // Seharusnya setiap method memeriksa peran dan kepemilikan.

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Course $course): bool
    {
        // Seharusnya:
        // return $user->role === 'admin' || $course->lecturer_id === $user->id;
        return true;
    }

    public function delete(User $user, Course $course): bool
    {
        // Seharusnya:
        // return $user->role === 'admin';
        return true;
    }
}
