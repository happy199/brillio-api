<?php

namespace Database\Factories;

use App\Models\MentoringSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentoringSessionFactory extends Factory
{
    protected $model = MentoringSession::class;

    public function definition(): array
    {
        return [
            'mentor_id' => User::factory()->mentor(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'duration_minutes' => 60,
            'is_paid' => false,
            'price' => 0,
            'status' => 'confirmed',
            'created_by' => 'mentor',
        ];
    }

    public function proposed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'proposed',
            'created_by' => 'mentee',
        ]);
    }
}
