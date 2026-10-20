<?php

namespace App\Livewire;

use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionList extends Component
{
    public int $assignmentId;

    public function mount(int $assignmentId): void
    {
        $this->assignmentId = $assignmentId;
    }

    // BUG 7 (w09): Method download() tidak ada Gate::authorize().
    // Template Blade menyembunyikan tombol dengan @if($submission->user_id === auth()->id())
    // tapi method ini bisa dipanggil langsung lewat HTTP POST ke Livewire endpoint.
    // Mahasiswa A bisa memanggil download(99) untuk mengunduh milik Mahasiswa B.
    //
    // Solusi: tambahkan Gate::authorize('view', $submission) sebelum Storage::download()
    public function download(int $submissionId): StreamedResponse
    {
        $submission = Submission::findOrFail($submissionId);

        // Gate::authorize('view', $submission);  // <- seharusnya ada di sini!

        abort_unless(Storage::disk('public')->exists($submission->file_path), 404);
        return Storage::disk('public')->download($submission->file_path, $submission->original_name);
    }

    public function render()
    {
        $submissions = Submission::where('assignment_id', $this->assignmentId)
            ->with('user')
            ->latest()
            ->get();

        return view('livewire.submission-list', compact('submissions'));
    }
}

