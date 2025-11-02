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

    public function test_puzzle_belongs_to_competition(): void
    {
        $competition = Competition::factory()->create();
        $puzzle = Puzzle::factory()->create(['competition_id' => $competition->id]);

        $this->assertInstanceOf(Competition::class, $puzzle->competition);
        $this->assertEquals($competition->id, $puzzle->competition->id);
    }

    public function test_puzzle_belongs_to_team(): void
    {
        $team = Team::factory()->create();
        $puzzle = Puzzle::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $puzzle->team);
        $this->assertEquals($team->id, $puzzle->team->id);
    }

    public function test_generate_ciphertext_creates_encrypted_text(): void
    {
        $puzzle = new Puzzle([
            'plaintext' => 'ABC',
            'shift' => 1,
        ]);

        $puzzle->generateCiphertext();

        $this->assertEquals('BCD', $puzzle->ciphertext);
    }

    public function test_generate_ciphertext_with_different_shifts(): void
    {
        $puzzle = new Puzzle([
            'plaintext' => 'Hello World',
            'shift' => 13,
        ]);

        $puzzle->generateCiphertext();

        $this->assertEquals('Uryyb Jbeyq', $puzzle->ciphertext);
    }

    public function test_validate_solution_returns_true_for_correct_answer(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertTrue($puzzle->validateSolution('Hello World'));
    }

    public function test_validate_solution_returns_false_for_incorrect_answer(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertFalse($puzzle->validateSolution('Wrong Answer'));
    }

    public function test_validate_solution_is_case_insensitive(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertTrue($puzzle->validateSolution('hello world'));
        $this->assertTrue($puzzle->validateSolution('HELLO WORLD'));
        $this->assertTrue($puzzle->validateSolution('HeLLo WoRLd'));
    }

    public function test_validate_solution_trims_whitespace(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'Hello World',
        ]);

        $this->assertTrue($puzzle->validateSolution('  Hello World  '));
        $this->assertTrue($puzzle->validateSolution("\tHello World\n"));
    }

    public function test_save_generates_ciphertext_when_plaintext_changes(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'ABC',
            'shift' => 1,
        ]);

        $puzzle->plaintext = 'XYZ';
        $puzzle->save();

        $this->assertEquals('YZA', $puzzle->ciphertext);
    }

    public function test_save_generates_ciphertext_when_shift_changes(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'ABC',
            'shift' => 1,
        ]);

        $puzzle->shift = 2;
        $puzzle->save();

        $this->assertEquals('CDE', $puzzle->ciphertext);
    }

    public function test_save_does_not_regenerate_ciphertext_when_unchanged(): void
    {
        $puzzle = Puzzle::factory()->create([
            'plaintext' => 'ABC',
            'shift' => 1,
            'ciphertext' => 'BCD',
        ]);

        $originalCiphertext = $puzzle->ciphertext;
        $puzzle->touch();
        $puzzle->save();

        $this->assertEquals($originalCiphertext, $puzzle->ciphertext);
    }
}
