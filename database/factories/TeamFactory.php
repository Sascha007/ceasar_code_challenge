<?php

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
        $displayName = fake()->unique()->words(2, true);

        return [
            'competition_id' => Competition::factory(),
            'slug' => Str::slug($displayName).'-'.fake()->unique()->numberBetween(1000, 9999),
            'display_name' => $displayName,
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
            'ready_at' => now()->subMinutes(10),
            'solved_at' => now(),
            'attempts' => fake()->numberBetween(1, 5),
        ]);
    }
}
