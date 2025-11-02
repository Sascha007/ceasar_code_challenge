<?php

namespace Database\Factories;

use App\Domain\Caesar\CaesarService;
use App\Models\Competition;
use App\Models\Puzzle;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Puzzle>
 */
class PuzzleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $plaintext = fake()->sentence(5);
        $shift = fake()->numberBetween(1, 25);
        $caesarService = new CaesarService;
        
        return [
            'competition_id' => Competition::factory(),
            'team_id' => null,
            'plaintext' => $plaintext,
            'ciphertext' => $caesarService->encode($plaintext, $shift),
            'shift' => $shift,
            'difficulty' => fake()->randomElement([Puzzle::DIFFICULTY_EASY, Puzzle::DIFFICULTY_MEDIUM, Puzzle::DIFFICULTY_HARD]),
            'points' => fake()->randomElement([50, 100, 150, 200]),
        ];
    }

    /**
     * Indicate that the puzzle is assigned to a team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
            'competition_id' => $team->competition_id,
        ]);
    }

    /**
     * Create a puzzle with a specific shift value.
     */
    public function withShift(int $shift): static
    {
        return $this->state(function (array $attributes) use ($shift) {
            $caesarService = new CaesarService;
            return [
                'shift' => $shift,
                'ciphertext' => $caesarService->encode($attributes['plaintext'], $shift),
            ];
        });
    }

    /**
     * Create a puzzle with specific plaintext.
     */
    public function withPlaintext(string $plaintext): static
    {
        return $this->state(function (array $attributes) use ($plaintext) {
            $caesarService = new CaesarService;
            return [
                'plaintext' => $plaintext,
                'ciphertext' => $caesarService->encode($plaintext, $attributes['shift']),
            ];
        });
    }
}
