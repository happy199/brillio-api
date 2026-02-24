<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorshipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mentor_id' => $this->mentor_id,
            'mentee_id' => $this->mentee_id,
            'status' => $this->status,
            'created_at' => $this->created_at->toISOString(),
            'mentor' => new UserResource($this->whenLoaded('mentor')),
            'mentee' => new UserResource($this->whenLoaded('mentee')),
        ];
    }
}
