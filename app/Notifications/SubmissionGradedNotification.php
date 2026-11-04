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

    // BUG 3 (w10): Menyimpan skor dan catatan feedback langsung ke payload notifikasi!
    // Kolom 'data' di tabel notifications menjadi terekspos dalam teks terbuka JSON.
    // Jika nilai direvisi dosen nanti, teks notifikasi ini tetap bernilai lama (usang).
    // Nilai adalah data akademik rahasia yang tidak boleh disimpan di payload notifikasi.
    //
    // Solusi:
    // return [
    //     'submission_id' => $this->submission->id,
    //     'assignment_id' => $this->submission->assignment_id,
    //     'title'         => $this->submission->assignment->title,
    //     'message'       => 'Tugas Anda untuk ' . $this->submission->assignment->title . ' telah dinilai.',
    // ];
    public function toArray(object $notifiable): array
    {
        return [
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'title'         => $this->submission->assignment->title,
            'score'         => $this->grade->score,       // BUG 3: Nilai bocor ke payload JSON!
            'feedback'      => $this->grade->feedback,    // BUG 3: Catatan feedback bocor ke payload!
            'message'       => 'Tugas Anda telah dinilai dengan nilai: ' . $this->grade->score, // BUG 3!
        ];
    }
}
