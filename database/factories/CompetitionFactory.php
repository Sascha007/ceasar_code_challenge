<?php

namespace Database\Factories;

use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Competition>
 */
class CompetitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'status' => Competition::STATUS_DRAFT,
            'started_at' => null,
            'finished_at' => null,
        ];
    }

    /**
     * Indicate that the competition is ready.
     */
    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Competition::STATUS_READY,
        ]);
    }

    /**
     * Indicate that the competition is running.
     */
    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Competition::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the competition is finished.
     */
    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Competition::STATUS_FINISHED,
            'started_at' => now()->subHour(),
            'finished_at' => now(),
        ]);
    }
}
