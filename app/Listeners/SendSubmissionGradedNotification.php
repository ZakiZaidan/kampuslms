<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSubmissionGradedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 10;
    public int $timeout = 60;

    public function handle(SubmissionGraded $event): void
    {
        $student = $event->submission->user;
        if ($student) {
            $student->notify(new SubmissionGradedNotification($event->submission, $event->grade));
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::critical('Gagal mengirimkan notifikasi penilaian', [
            'error' => $exception?->getMessage(),
        ]);
    }
}
