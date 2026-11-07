<?php

namespace App\Notifications;

use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Submission $submission;
    public Grade $grade;

    public function __construct(Submission $submission, Grade $grade)
    {
        $this->submission = $submission;
        $this->grade      = $grade;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'title'         => $this->submission->assignment?->title ?? 'Tugas',
            'message'       => 'Tugas Anda untuk ' . ($this->submission->assignment?->title ?? 'Tugas') . ' telah dinilai.',
        ];
    }
}
