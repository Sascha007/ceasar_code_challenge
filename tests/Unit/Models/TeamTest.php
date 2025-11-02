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

    public function test_team_can_be_created_with_factory(): void
    {
        $team = Team::factory()->create();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_team_belongs_to_competition(): void
    {
        $competition = Competition::factory()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);

        $this->assertEquals($competition->id, $team->competition->id);
    }

    public function test_team_can_be_marked_ready(): void
    {
        $team = Team::factory()->ready()->create();

        $this->assertTrue($team->isReady());
        $this->assertNotNull($team->ready_at);
    }

    public function test_team_can_be_marked_solved(): void
    {
        $team = Team::factory()->solved()->create();

        $this->assertTrue($team->isSolved());
        $this->assertNotNull($team->solved_at);
    }

    public function test_team_rate_limiting_allows_submissions_within_limit(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->forTeam($team)->create();

        // Create 3 submissions (less than default limit of 5)
        for ($i = 0; $i < 3; $i++) {
            $team->submissions()->create([
                'puzzle_id' => $puzzle->id,
                'content' => "Attempt $i",
                'is_correct' => false,
            ]);
        }

        $this->assertFalse($team->isRateLimited());
    }

    public function test_team_rate_limiting_blocks_excessive_submissions(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->forTeam($team)->create();

        // Create 5 submissions (equal to default limit)
        for ($i = 0; $i < 5; $i++) {
            $team->submissions()->create([
                'puzzle_id' => $puzzle->id,
                'content' => "Attempt $i",
                'is_correct' => false,
            ]);
        }

        $this->assertTrue($team->isRateLimited());
    }

    public function test_team_rate_limiting_respects_time_decay(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->forTeam($team)->create();

        // Create 5 old submissions (more than 2 minutes ago)
        for ($i = 0; $i < 5; $i++) {
            $submission = $team->submissions()->create([
                'puzzle_id' => $puzzle->id,
                'content' => "Old Attempt $i",
                'is_correct' => false,
            ]);
            $submission->created_at = now()->subMinutes(3);
            $submission->save();
        }

        // Should not be rate limited as submissions are old
        $this->assertFalse($team->isRateLimited());
    }

    public function test_team_record_attempt_prevents_submissions_when_rate_limited(): void
    {
        $competition = Competition::factory()->running()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $puzzle = Puzzle::factory()->forTeam($team)->create();

        // Fill up the rate limit
        for ($i = 0; $i < 5; $i++) {
            $team->submissions()->create([
                'puzzle_id' => $puzzle->id,
                'content' => "Attempt $i",
                'is_correct' => false,
            ]);
        }

        // Try to record another attempt
        $result = $team->recordAttempt('Another attempt');

        $this->assertFalse($result);
    }
}
