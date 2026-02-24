<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MentorProfile>
 */
class MentorProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->mentor(),
            'bio' => fake()->paragraph(),
            'advice' => fake()->sentence(),
            'current_position' => fake()->jobTitle(),
            'current_company' => fake()->company(),
            'years_of_experience' => fake()->numberBetween(1, 40),
            'specialization' => fake()->randomElement(['tech', 'finance', 'health', 'education']),
            'is_published' => true,
            'is_validated' => true,
            'validated_at' => now(),
        ];
    }
}