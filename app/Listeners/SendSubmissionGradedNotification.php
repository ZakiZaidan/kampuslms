<?php

namespace App\Listeners;

use App\Events\SubmissionGraded;
use App\Notifications\SubmissionGradedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendSubmissionGradedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    // BUG 6 (w10): Tidak memiliki properti $tries, $backoff, dan method failed().
    // Jika koneksi database atau layanan antrean mengalami gangguan sementara,
    // job ini tidak memiliki strategi retry yang terukur dan jika gagal permanen,
    // tidak ada pencatatan log peringatan (Log::critical) untuk pemantauan sistem.
    //
    // Solusi:
    // public int $tries = 3;
    // public int $backoff = 10;
    // public function failed(?\Throwable $exception): void {
    //     \Illuminate\Support\Facades\Log::critical('Gagal mengirim notifikasi penilaian: ' . $exception?->getMessage());
    // }

    public function handle(SubmissionGraded $event): void
    {
        $student = $event->submission->user;
        $student->notify(new SubmissionGradedNotification($event->submission, $event->grade));
    }
}
