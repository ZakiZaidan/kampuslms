<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AssignmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Assignment $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'course_id'     => $this->assignment->course_id,
            'title'         => $this->assignment->title,
            'message'       => 'Tugas baru telah dipublikasikan: ' . $this->assignment->title,
        ];
    }
}
