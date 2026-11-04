<?php

namespace App\Listeners;

use App\Events\AssignmentCreated;
use App\Notifications\AssignmentCreatedNotification;
use Illuminate\Support\Facades\Notification;

// BUG 1 (w10): Tidak mengimplementasikan ShouldQueue!
// Listener ini berjalan secara SINKRON pada thread web request saat dosen membuat tugas baru.
// Jika kelas memiliki 50 mahasiswa, web request dosen akan tertahan/hang selama berdetik-detik
// bahkan dapat melempar 504 Gateway Timeout.
//
// Solusi:
// use Illuminate\Contracts\Queue\ShouldQueue;
// class SendAssignmentCreatedNotification implements ShouldQueue
class SendAssignmentCreatedNotification // implements ShouldQueue <- HILANG (BUG 1)
{
    public function handle(AssignmentCreated $event): void
    {
        $course = $event->assignment->course;
        $students = $course->students;

        // BUG 2 (w10): Notifikasi dikirim dalam loop satu per satu (N+1 Notification Query).
        // Setiap iterasi memicu query INSERT terpisah ke tabel notifications secara berulang-ulang.
        //
        // Solusi:
        // Notification::send($students, new AssignmentCreatedNotification($event->assignment));
        foreach ($students as $student) {
            $student->notify(new AssignmentCreatedNotification($event->assignment)); // BUG 2!
        }
    }
}
