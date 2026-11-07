<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Assignment $assignment;

    public function __construct(Assignment $assignment)
    {
        $this->assignment = $assignment;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('courses.' . $this->assignment->course_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title'         => $this->assignment->title,
            'course_id'     => $this->assignment->course_id,
            'due_at'        => $this->assignment->due_at?->toIso8601String(),
        ];
    }
}
