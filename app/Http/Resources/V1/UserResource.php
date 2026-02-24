<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'user_type' => $this->user_type,
            'phone' => $this->phone,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'country' => $this->country,
            'city' => $this->city,
            'profile_photo_url' => $this->profile_photo_url,
            'linkedin_url' => $this->linkedin_url,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'personality_test' => $this->whenLoaded('personalityTest', function () {
                return $this->personalityTest ? [
                    'type' => $this->personalityTest->personality_type,
                    'label' => $this->personalityTest->personality_label,
                    'completed_at' => $this->personalityTest->completed_at?->toISOString(),
                ] : null;
            }),
            'mentor_profile' => $this->whenLoaded('mentorProfile', function () {
                return $this->mentorProfile ? [
                    'id' => $this->mentorProfile->id,
                    'is_published' => $this->mentorProfile->is_published,
                    'specialization' => $this->mentorProfile->specialization,
                ] : null;
            }),
        ];
    }
}