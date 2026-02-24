<?php

namespace Database\Factories;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentorshipFactory extends Factory
{
    protected $model = Mentorship::class;

    public function definition(): array
    {
        return [
            'mentor_id' => User::factory()->mentor(),
            'mentee_id' => User::factory(),
            'status' => 'pending',
            'request_message' => $this->faker->sentence(),
        ];
    }

    public function accepted(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }
}
