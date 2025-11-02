<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'competition_id' => Competition::factory(),
            'slug' => fake()->unique()->slug(2),
            'display_name' => fake()->words(2, true),
            'ready_at' => null,
            'solved_at' => null,
            'attempts' => 0,
        ];
    }

    /**
     * Indicate that the team is ready.
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'ready_at' => now(),
        ]);
    }

    /**
     * Indicate that the team has solved their puzzle.
     */
    public function solved(): static
    {
        return $this->state(fn (array $attributes) => [
            'ready_at' => now()->subMinutes(30),
            'solved_at' => now(),
            'attempts' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Set specific number of attempts.
     */
    public function withAttempts(int $attempts): static
    {
        return $this->state(fn (array $attributes) => [
            'attempts' => $attempts,
        ]);
    }
}
