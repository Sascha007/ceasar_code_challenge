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
        $plaintext = fake()->sentence();
        $shift = fake()->numberBetween(1, 25);
        $caesarService = new CaesarService;
        $ciphertext = $caesarService->encode($plaintext, $shift);

        return [
            'competition_id' => Competition::factory(),
            'team_id' => Team::factory(),
            'plaintext' => $plaintext,
            'ciphertext' => $ciphertext,
            'shift' => $shift,
        ];
    }

    /**
     * Indicate that the puzzle has a specific plaintext.
     */
    public function withPlaintext(string $plaintext): static
    {
        return $this->state(function (array $attributes) use ($plaintext) {
            $caesarService = new CaesarService;
            $ciphertext = $caesarService->encode($plaintext, $attributes['shift']);

            return [
                'plaintext' => $plaintext,
                'ciphertext' => $ciphertext,
            ];
        });
    }

    /**
     * Indicate that the puzzle has a specific shift value.
     */
    public function withShift(int $shift): static
    {
        return $this->state(function (array $attributes) use ($shift) {
            $caesarService = new CaesarService;
            $ciphertext = $caesarService->encode($attributes['plaintext'], $shift);

            return [
                'shift' => $shift,
                'ciphertext' => $ciphertext,
            ];
        });
    }
}
