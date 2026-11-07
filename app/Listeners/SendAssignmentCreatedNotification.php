<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendAssignmentCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;

    public function handle(AssignmentCreated $event): void
    {
        $course = $event->assignment->course;
        if (! $course) {
            return;
        }

        $students = $course->students;

        if ($students->isNotEmpty()) {
            Notification::send($students, new AssignmentCreatedNotification($event->assignment));
        }
    }
}
