<?php

namespace Tests\Unit\Models;

use App\Models\Competition;
use App\Models\Puzzle;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PuzzleTest extends TestCase
{
    use RefreshDatabase;

    public function test_puzzle_can_be_created_with_factory(): void
    {
        $puzzle = Puzzle::factory()->create();

        $this->assertDatabaseHas('puzzles', [
            'id' => $puzzle->id,
        ]);
    }

    public function test_puzzle_generates_ciphertext_on_save(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'HELLO',
            'shift' => 1,
        ]);

        $this->assertEquals('IFMMP', $puzzle->ciphertext);
    }

    public function test_puzzle_validates_correct_solution(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertTrue($puzzle->validateSolution('Hello World'));
        $this->assertTrue($puzzle->validateSolution('hello world')); // case insensitive
        $this->assertTrue($puzzle->validateSolution(' Hello World ')); // with whitespace
    }

    public function test_puzzle_rejects_incorrect_solution(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertFalse($puzzle->validateSolution('Wrong Answer'));
    }

    public function test_puzzle_belongs_to_competition(): void
    {
        $competition = Competition::factory()->create();
        $puzzle = Puzzle::factory()->create(['competition_id' => $competition->id]);

        $this->assertEquals($competition->id, $puzzle->competition->id);
    }

    public function test_puzzle_can_be_assigned_to_team(): void
    {
        $team = Team::factory()->create();
        $puzzle = Puzzle::factory()->forTeam($team)->create();

        $this->assertEquals($team->id, $puzzle->team->id);
    }

    public function test_puzzle_has_difficulty_levels(): void
    {
        $easyPuzzle = Puzzle::factory()->create(['difficulty' => Puzzle::DIFFICULTY_EASY]);
        $mediumPuzzle = Puzzle::factory()->create(['difficulty' => Puzzle::DIFFICULTY_MEDIUM]);
        $hardPuzzle = Puzzle::factory()->create(['difficulty' => Puzzle::DIFFICULTY_HARD]);

        $this->assertEquals(Puzzle::DIFFICULTY_EASY, $easyPuzzle->difficulty);
        $this->assertEquals(Puzzle::DIFFICULTY_MEDIUM, $mediumPuzzle->difficulty);
        $this->assertEquals(Puzzle::DIFFICULTY_HARD, $hardPuzzle->difficulty);
    }

    public function test_puzzle_has_points_system(): void
    {
        $puzzle = Puzzle::factory()->create(['points' => 150]);

        $this->assertEquals(150, $puzzle->points);
    }
}
