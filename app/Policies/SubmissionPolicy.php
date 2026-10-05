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

    // BUG 8: N+1 di dalam Policy.
    // Setiap pemanggilan view() untuk submission dalam sebuah loop
    // akan menjalankan dua query relasi tambahan per submission:
    //   $submission->assignment → query
    //   $submission->assignment->course → query lagi
    // Dengan 20 submission = 41 query hanya untuk policy check.
    // Seharusnya relasi sudah di-eager load sebelum policy dipanggil,
    // atau pakai $submission->assignment_id dan preload dari controller.
    public function view(User $user, Submission $submission): bool
    {
        return $user->id === $submission->user_id
            || $user->role === 'admin'
            || $submission->assignment->course->lecturer_id === $user->id;
        //   ↑↑ Ini memicu query per submission (N+1)
    }

    public function create(User $user): bool
    {
        return $user->role === 'mahasiswa';
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
