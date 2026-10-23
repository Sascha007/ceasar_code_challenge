<?php

namespace Tests\Unit\Models;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_has_many_teams(): void
    {
        $competition = Competition::factory()->create();
        Team::factory()->count(3)->create(['competition_id' => $competition->id]);

        $this->assertCount(3, $competition->teams);
    }

    public function test_competition_has_many_puzzles(): void
    {
        $competition = Competition::factory()->create();
        $team = Team::factory()->create(['competition_id' => $competition->id]);
        $team->puzzle()->create([
            'competition_id' => $competition->id,
            'plaintext' => 'Test',
            'ciphertext' => 'Whvw',
            'shift' => 3,
        ]);

        $this->assertCount(1, $competition->puzzles);
    }

    public function test_can_start_returns_true_when_ready_with_teams(): void
    {
        $competition = Competition::factory()->ready()->create();
        Team::factory()->create(['competition_id' => $competition->id]);

        $this->assertTrue($competition->canStart());
    }

    public function test_can_start_returns_false_when_not_ready(): void
    {
        $competition = Competition::factory()->create(['status' => Competition::STATUS_DRAFT]);
        Team::factory()->create(['competition_id' => $competition->id]);

        $this->assertFalse($competition->canStart());
    }

    public function test_can_start_returns_false_when_no_teams(): void
    {
        $competition = Competition::factory()->ready()->create();

        $this->assertFalse($competition->canStart());
    }

    public function test_start_changes_status_to_running(): void
    {
        $competition = Competition::factory()->ready()->create();
        Team::factory()->create(['competition_id' => $competition->id]);

        $result = $competition->start();

        $this->assertTrue($result);
        $this->assertEquals(Competition::STATUS_RUNNING, $competition->refresh()->status);
        $this->assertNotNull($competition->started_at);
    }

    public function test_start_returns_false_when_cannot_start(): void
    {
        $competition = Competition::factory()->create(['status' => Competition::STATUS_DRAFT]);

        $result = $competition->start();

        $this->assertFalse($result);
    }

    public function test_finish_changes_status_to_finished(): void
    {
        $competition = Competition::factory()->running()->create();

        $result = $competition->finish();

        $this->assertTrue($result);
        $this->assertEquals(Competition::STATUS_FINISHED, $competition->refresh()->status);
        $this->assertNotNull($competition->finished_at);
    }

    public function test_finish_returns_false_when_not_running(): void
    {
        $competition = Competition::factory()->ready()->create();

        $result = $competition->finish();

        $this->assertFalse($result);
    }

    public function test_check_all_teams_solved_returns_true_when_all_solved(): void
    {
        $competition = Competition::factory()->running()->create();
        Team::factory()->count(2)->create([
            'competition_id' => $competition->id,
            'solved_at' => now(),
        ]);

        $result = $competition->checkAllTeamsSolved();

        $this->assertTrue($result);
        $this->assertEquals(Competition::STATUS_FINISHED, $competition->refresh()->status);
    }

    public function test_check_all_teams_solved_returns_false_when_not_all_solved(): void
    {
        $competition = Competition::factory()->running()->create();
        Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => now(),
        ]);
        Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => null,
        ]);

        $result = $competition->checkAllTeamsSolved();

        $this->assertFalse($result);
        $this->assertEquals(Competition::STATUS_RUNNING, $competition->refresh()->status);
    }

    public function test_get_rankings_orders_by_solve_time_and_attempts(): void
    {
        $competition = Competition::factory()->running()->create();
        $team1 = Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => now()->addMinutes(2),
            'attempts' => 3,
        ]);
        $team2 = Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => now()->addMinutes(1),
            'attempts' => 1,
        ]);
        $team3 = Team::factory()->create([
            'competition_id' => $competition->id,
            'solved_at' => null,
            'attempts' => 0,
        ]);

        $rankings = $competition->getRankings();

        $this->assertCount(2, $rankings);
        $this->assertEquals($team2->id, $rankings->first()->id);
        $this->assertEquals($team1->id, $rankings->last()->id);
    }

    public function test_reset_clears_competition_state(): void
    {
        $competition = Competition::factory()->finished()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
            'ready_at' => now(),
            'solved_at' => now(),
            'attempts' => 5,
        ]);

        $result = $competition->reset();

        $this->assertTrue($result);
        $competition->refresh();
        $team->refresh();

        $this->assertEquals(Competition::STATUS_READY, $competition->status);
        $this->assertNull($competition->started_at);
        $this->assertNull($competition->finished_at);
        $this->assertNull($team->ready_at);
        $this->assertNull($team->solved_at);
        $this->assertEquals(0, $team->attempts);
    }
}
