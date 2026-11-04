<?php

namespace App\Events;

use App\Models\Grade;
use App\Models\Submission;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubmissionGraded
{
    use Dispatchable, SerializesModels;

    public Submission $submission;
    public Grade $grade;

    public function __construct(Submission $submission, Grade $grade)
    {
        $this->submission = $submission;
        $this->grade      = $grade;
    }
}
