<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Submission $submission): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->id === $submission->user_id) {
            return true;
        }

        $course = $submission->assignment?->course;
        return $course && $course->lecturer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['mahasiswa', 'student']);
    }

    public function update(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id;
    }

    public function delete(User $user, Submission $submission): bool
    {
        return $user->role === 'admin';
    }
}
