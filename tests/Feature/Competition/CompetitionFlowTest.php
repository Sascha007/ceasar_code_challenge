<?php

namespace Tests\Feature\Competition;

use App\Models\Competition;
use App\Models\Puzzle;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionFlowTest extends TestCase
{
    use RefreshDatabase;

    private Competition $competition;
    private Team $team1;
    private Team $team2;
    private Puzzle $puzzle1;
    private Puzzle $puzzle2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a competition with two teams
        $this->competition = Competition::create([
            'name' => 'Test Competition',
            'status' => Competition::STATUS_DRAFT,
        ]);

        $this->team1 = $this->competition->teams()->create([
            'slug' => 'team1',
            'display_name' => 'Team One',
        ]);

        $this->team2 = $this->competition->teams()->create([
            'slug' => 'team2',
            'display_name' => 'Team Two',
        ]);

        // Create puzzles for each team
        $this->puzzle1 = Puzzle::create([
            'competition_id' => $this->competition->id,
            'team_id' => $this->team1->id,
            'plaintext' => 'Hello World',
            'shift' => 3,
        ]);

        $this->puzzle2 = Puzzle::create([
            'competition_id' => $this->competition->id,
            'team_id' => $this->team2->id,
            'plaintext' => 'Test Message',
            'shift' => 5,
        ]);
    }

    public function test_competition_flow(): void
    {
        // Initially competition is in draft status
        $this->assertEquals(Competition::STATUS_DRAFT, $this->competition->status);

        // Set competition to ready
        $this->competition->update(['status' => Competition::STATUS_READY]);

        // Teams can mark themselves as ready
        $this->team1->markReady();
        $this->team2->markReady();

        $this->assertTrue($this->team1->isReady());
        $this->assertTrue($this->team2->isReady());

        // Start the competition
        $this->assertTrue($this->competition->canStart());
        $this->competition->start();

        $this->assertEquals(Competition::STATUS_RUNNING, $this->competition->status);
        $this->assertNotNull($this->competition->started_at);

        // Team 1 makes incorrect submission
        $wrongAnswer = 'Wrong Answer';
        $this->assertFalse($this->team1->recordAttempt($wrongAnswer));
        $this->assertEquals(1, $this->team1->attempts);
        $this->assertNull($this->team1->solved_at);

        // Team 1 makes correct submission
        $this->assertTrue($this->team1->recordAttempt('Hello World'));
        $this->assertEquals(2, $this->team1->attempts);
        $this->assertNotNull($this->team1->solved_at);

        // Team 2 makes correct submission in first try
        $this->assertTrue($this->team2->recordAttempt('Test Message'));
        $this->assertEquals(1, $this->team2->attempts);
        $this->assertNotNull($this->team2->solved_at);

        // Finish competition
        $this->competition->finish();
        $this->assertEquals(Competition::STATUS_FINISHED, $this->competition->status);
        $this->assertNotNull($this->competition->finished_at);

        // Reset competition
        $this->competition->reset();
        $this->assertEquals(Competition::STATUS_READY, $this->competition->status);
        $this->assertNull($this->competition->started_at);
        $this->assertNull($this->competition->finished_at);

        // Teams should be reset
        $this->team1->refresh();
        $this->team2->refresh();
        $this->assertEquals(0, $this->team1->attempts);
        $this->assertEquals(0, $this->team2->attempts);
        $this->assertNull($this->team1->solved_at);
        $this->assertNull($this->team2->solved_at);
    }
}
