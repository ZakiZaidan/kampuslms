<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'course_id'   => $this->course_id,
            'title'       => $this->title,
            'description' => $this->description,
            'due_date'    => $this->due_date?->toIso8601String(),
            'created_at'  => $this->created_at?->toIso8601String(),
        ];
    }
}
