<?php

namespace App\Events;

use App\Models\Assignment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
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

    // BUG 7 (w10): Menggunakan Channel publik alih-alih PrivateChannel.
    // Siapa pun di frontend (bahkan tanpa login atau mahasiswa luar)
    // bisa mendengarkan channel ini dan menyadap tugas baru secara realtime.
    //
    // Solusi:
    // return [
    //     new \Illuminate\Broadcasting\PrivateChannel('courses.' . $this->assignment->course_id),
    // ];
    public function broadcastOn(): array
    {
        return [
            new Channel('course.' . $this->assignment->course_id), // BUG 7: Channel publik tanpa otorisasi!
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
