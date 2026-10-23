<?php

namespace Tests\Unit\Models;

use App\Models\Competition;
use App\Models\Puzzle;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_belongs_to_competition(): void
    {
        $competition = Competition::factory()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);

        $this->assertInstanceOf(Competition::class, $team->competition);
        $this->assertEquals($competition->id, $team->competition->id);
    }

    public function test_team_has_puzzle(): void
    {
        $team = Team::factory()->create();
        $puzzle = Puzzle::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Puzzle::class, $team->puzzle);
        $this->assertEquals($puzzle->id, $team->puzzle->id);
    }

    public function test_team_has_many_submissions(): void
    {
        $team = Team::factory()->create();
        $team->submissions()->create([
            'content' => 'Test submission',
            'is_correct' => false,
        ]);

        $this->assertCount(1, $team->submissions);
    }

    public function test_is_ready_returns_false_when_ready_at_is_null(): void
    {
        $team = Team::factory()->create(['ready_at' => null]);

        $this->assertFalse($team->isReady());
    }

    public function test_is_ready_returns_true_when_ready_at_is_set(): void
    {
        $team = Team::factory()->create(['ready_at' => now()]);

        $this->assertTrue($team->isReady());
    }

    public function test_mark_ready_sets_ready_at_timestamp(): void
    {
        $competition = Competition::factory()->ready()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'ready_at' => null,
        ]);

        $result = $team->markReady();

        $this->assertTrue($result);
        $this->assertNotNull($team->refresh()->ready_at);
    }

    public function test_mark_ready_returns_false_if_already_ready(): void
    {
        $competition = Competition::factory()->ready()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'ready_at' => now(),
        ]);

        $result = $team->markReady();

        $this->assertFalse($result);
    }

    public function test_mark_ready_returns_false_if_competition_not_ready(): void
    {
        $competition = Competition::factory()->create(['status' => Competition::STATUS_DRAFT]);
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'ready_at' => null,
        ]);

        $result = $team->markReady();

        $this->assertFalse($result);
    }

    public function test_is_solved_returns_false_when_solved_at_is_null(): void
    {
        $team = Team::factory()->create(['solved_at' => null]);

        $this->assertFalse($team->isSolved());
    }

    public function test_is_solved_returns_true_when_solved_at_is_set(): void
    {
        $team = Team::factory()->create(['solved_at' => now()]);

        $this->assertTrue($team->isSolved());
    }

    public function test_mark_solved_sets_solved_at_timestamp(): void
    {
        $team = Team::factory()->create(['solved_at' => null]);

        $result = $team->markSolved();

        $this->assertTrue($result);
        $this->assertNotNull($team->refresh()->solved_at);
    }

    public function test_mark_solved_returns_false_if_already_solved(): void
    {
        $team = Team::factory()->create(['solved_at' => now()]);

        $result = $team->markSolved();

        $this->assertFalse($result);
    }

    public function test_record_attempt_increments_attempts_count(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'attempts' => 0,
        ]);
        $puzzle = Puzzle::factory()->create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
            'plaintext' => 'Hello World',
        ]);

        $team->recordAttempt('Wrong Answer');

        $this->assertEquals(1, $team->refresh()->attempts);
    }

    public function test_record_attempt_creates_submission(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
            'plaintext' => 'Hello World',
        ]);

        $team->recordAttempt('Test Answer');

        $this->assertCount(1, $team->submissions);
        $this->assertEquals('Test Answer', $team->submissions->first()->content);
    }

    public function test_record_attempt_marks_team_as_solved_on_correct_answer(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => null,
        ]);
        $puzzle = Puzzle::factory()->create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
            'plaintext' => 'Hello World',
        ]);

        $team->recordAttempt('Hello World');

        $this->assertNotNull($team->refresh()->solved_at);
    }

    public function test_record_attempt_returns_false_if_competition_not_running(): void
    {
        $competition = Competition::factory()->create(['status' => Competition::STATUS_READY]);
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
        ]);

        $result = $team->recordAttempt('Test Answer');

        $this->assertFalse($result);
    }

    public function test_record_attempt_returns_false_if_already_solved(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => now(),
        ]);
        $puzzle = Puzzle::factory()->create([
            'team_id' => $team->id,
            'competition_id' => $competition->id,
        ]);

        $result = $team->recordAttempt('Test Answer');

        $this->assertFalse($result);
    }
}
