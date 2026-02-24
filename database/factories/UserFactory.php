<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'user_type' => User::TYPE_JEUNE,
            'onboarding_completed' => true,
            'is_admin' => false,
            'is_archived' => false,
            'is_blocked' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
        'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
        'is_admin' => true,
        ]);
    }

    /**
     * Indicate that the user is a mentor.
     */
    public function mentor(): static
    {
        return $this->state(fn(array $attributes) => [
        'user_type' => User::TYPE_MENTOR,
        ]);
    }

    /**
     * Indicate that the user is an organization.
     */
    public function organization(): static
    {
        return $this->state(fn(array $attributes) => [
        'user_type' => User::TYPE_ORGANIZATION,
        ]);
    }

    /**
     * Indicate that the user is archived.
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
        'is_archived' => true,
        'archived_at' => now(),
        'archived_reason' => 'Archivage de test',
        ]);
    }
}