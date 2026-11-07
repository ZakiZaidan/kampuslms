<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\Material;
use App\Models\User;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Material $material): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        $course = $material->course;
        if (! $course) {
            return false;
        }

        if (in_array($user->role, ['dosen', 'lecturer']) && $course->lecturer_id === $user->id) {
            return true;
        }

        return $course->students()->where('user_id', $user->id)->exists();
    }

    public function create(User $user, Course $course): bool
    {
        return $user->role === 'admin' || (in_array($user->role, ['dosen', 'lecturer']) && $course->lecturer_id === $user->id);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->role === 'admin' || (in_array($user->role, ['dosen', 'lecturer']) && $course->lecturer_id === $user->id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->role === 'admin' || (in_array($user->role, ['dosen', 'lecturer']) && $course->lecturer_id === $user->id);
    }
}
