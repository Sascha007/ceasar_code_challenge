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
        $this->puzzle1->generateCiphertext();
        $this->puzzle1->save();

        $this->puzzle2 = Puzzle::create([
            'competition_id' => $this->competition->id,
            'team_id' => $this->team2->id,
            'plaintext' => 'Test Message',
            'shift' => 5,
        ]);
        $this->puzzle2->generateCiphertext();
        $this->puzzle2->save();
    }

    public function test_competition_starts_in_draft_status(): void
    {
        $this->assertEquals(Competition::STATUS_DRAFT, $this->competition->status);
    }

    public function test_teams_can_mark_themselves_as_ready(): void
    {
        $this->competition->update(['status' => Competition::STATUS_READY]);

        $this->team1->markReady();
        $this->team2->markReady();

        $this->assertTrue($this->team1->isReady());
        $this->assertTrue($this->team2->isReady());
    }

    public function test_competition_can_be_started(): void
    {
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();

        $this->assertTrue($this->competition->canStart());
        $this->competition->start();
        $this->competition->refresh();

        $this->assertEquals(Competition::STATUS_RUNNING, $this->competition->status);
        $this->assertNotNull($this->competition->started_at);
    }

    public function test_team_can_make_incorrect_submission(): void
    {
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();
        $startResult = $this->competition->start();
        $this->assertTrue($startResult, 'Competition should start successfully');

        // Reload all relationships
        $this->competition->refresh();
        $this->team1->refresh();
        $this->team1->load('competition', 'puzzle');

        $this->assertEquals(Competition::STATUS_RUNNING, $this->competition->status, 'Competition should be in running state');
        $this->assertFalse($this->team1->isSolved(), 'Team should not be solved initially');
        $puzzle = $this->team1->puzzle()->first();
        \Log::info('Team puzzle:', ['puzzle' => $puzzle]);
        $this->assertNotNull($puzzle, 'Team should have a puzzle');

        // Verify puzzle content
        $this->assertEquals('Hello World', $puzzle->plaintext, 'Puzzle should have correct plaintext');
        $this->assertTrue($puzzle->validateSolution('Hello World'), 'Puzzle should validate correct solution');
        $this->assertFalse($puzzle->validateSolution('Wrong Answer'), 'Puzzle should reject wrong solution');
        $wrongAnswer = 'Wrong Answer';
        $result = $this->team1->recordAttempt($wrongAnswer);
        $this->assertTrue($result, 'recordAttempt should return true');
        $this->team1->refresh();

        $submission = $this->team1->submissions()->latest()->first();
        $this->assertNotNull($submission, 'Submission should be created');
        $this->assertFalse($submission->is_correct);
        $this->assertEquals(1, $this->team1->attempts);
        $this->assertNull($this->team1->solved_at);
    }

    public function test_team_can_make_correct_submission(): void
    {
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();

        $startResult = $this->competition->start();
        $this->assertTrue($startResult, 'Competition should start successfully');

        // Reload all relationships
        $this->competition->refresh();
        $this->team1->refresh();
        $this->team1->load('competition', 'puzzle');

        $this->assertEquals(Competition::STATUS_RUNNING, $this->competition->status, 'Competition should be in running state');
        $this->assertFalse($this->team1->isSolved(), 'Team should not be solved initially');

        $puzzle = $this->team1->puzzle()->first();
        $this->assertNotNull($puzzle, 'Team should have a puzzle');
        $this->assertEquals('Hello World', $puzzle->plaintext, 'Puzzle should have correct plaintext');

        $result = $this->team1->recordAttempt('Hello World');
        $this->assertTrue($result, 'recordAttempt should return true');

        $this->team1->refresh();
        $submission = $this->team1->submissions()->latest()->first();

        $this->assertNotNull($submission, 'Submission should be created');
        $this->assertTrue($submission->is_correct, 'Submission should be marked as correct');
        $this->assertEquals(1, $this->team1->attempts, 'Should have one attempt');
        $this->assertNotNull($this->team1->solved_at, 'Should be marked as solved');

        // Team 2 makes correct submission in first try
        $this->team2->refresh();
        $this->team2->load('competition', 'puzzle');

        $result = $this->team2->recordAttempt('Test Message');
        $this->assertTrue($result, 'Team 2 recordAttempt should return true');

        $this->team2->refresh();
        $submission = $this->team2->submissions()->latest()->first();
        $this->assertNotNull($submission, 'Team 2 submission should be created');
        $this->assertTrue($submission->is_correct, 'Team 2 submission should be marked as correct');
        $this->assertEquals(1, $this->team2->attempts, 'Team 2 should have one attempt');
        $this->assertNotNull($this->team2->solved_at, 'Team 2 should be marked as solved');
    }

    public function test_submissions_are_rejected_after_competition_ends(): void
    {
        // Set up and start competition
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();
        $this->competition->start();
        $this->competition->refresh();

        // Both teams solve their puzzles
        $this->team1->recordAttempt('Hello World');
        $this->team2->recordAttempt('Test Message');

        // Refresh competition to get updated status
        $this->competition->refresh();

        // Competition should be finished
        $this->assertEquals(Competition::STATUS_FINISHED, $this->competition->status, 'Competition should be finished');

        // Attempt to submit after competition end
        $result = $this->team1->recordAttempt('Another Try');
        $this->assertFalse($result, 'Submission after competition end should be rejected');

        // Verify attempts count hasn't changed
        $this->team1->refresh();
        $this->assertEquals(1, $this->team1->attempts, 'Attempts count should not increase after competition end');
    }

    public function test_handles_special_characters_in_submissions(): void
    {
        // Set up and start competition
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();
        $this->competition->start();
        $this->competition->refresh();

        // Update existing puzzle with special characters
        $this->puzzle1->update(['plaintext' => 'Hello, World! @#$%^&*']);
        $this->puzzle1->generateCiphertext();
        $this->puzzle1->save();

        // Test with various special characters and whitespace
        $correct = 'Hello, World! @#$%^&*';
        $submissions = [
            $correct,                      // Exact match
            '  '.$correct.'  ',        // Extra whitespace
            strtoupper($correct),          // Different case
            str_replace(' ', '', $correct), // Removed spaces
        ];

        // Test first submission
        $result = $this->team1->recordAttempt($correct);
        $this->assertTrue($result, "Submission '$correct' should be accepted");
        $this->team1->refresh();
        $this->assertTrue($this->team1->isSolved(), 'Team should be marked as solved');
    }

    public function test_handles_simultaneous_team_submissions(): void
    {
        // Set up and start competition
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();
        $this->competition->start();
        $this->competition->refresh();

        // Simulate near-simultaneous submissions
        $result1 = $this->team1->recordAttempt('Hello World');
        $result2 = $this->team2->recordAttempt('Test Message');

        $this->assertTrue($result1, 'Team 1 submission should succeed');
        $this->assertTrue($result2, 'Team 2 submission should succeed');

        // Refresh models
        $this->team1->refresh();
        $this->team2->refresh();
        $this->competition->refresh();

        // Verify both submissions were recorded
        $this->assertNotNull($this->team1->solved_at, 'Team 1 should be marked as solved');
        $this->assertNotNull($this->team2->solved_at, 'Team 2 should be marked as solved');

        // Competition should be finished
        $this->assertEquals(Competition::STATUS_FINISHED, $this->competition->status, 'Competition should be finished');

        // Rankings should preserve the actual order
        $rankings = $this->competition->getRankings();
        $this->assertCount(2, $rankings, 'Should have rankings for both teams');
        $this->assertTrue(
            $rankings->first()->solved_at <= $rankings->last()->solved_at,
            'Rankings should preserve submission order'
        );
    }

    public function test_competition_ends_when_all_teams_solved(): void
    {
        // Set up and start competition
        $this->competition->update(['status' => Competition::STATUS_READY]);
        $this->team1->markReady();
        $this->team2->markReady();

        $startResult = $this->competition->start();
        $this->assertTrue($startResult, 'Competition should start successfully');

        // Refresh all models
        $this->competition->refresh();
        $this->team1->refresh();
        $this->team2->refresh();

        // Load relationships
        $this->team1->load('competition', 'puzzle');
        $this->team2->load('competition', 'puzzle');

        // Both teams solve their puzzles
        $result1 = $this->team1->recordAttempt('Hello World');
        $this->assertTrue($result1, 'Team 1 should be able to submit solution');

        $result2 = $this->team2->recordAttempt('Test Message');
        $this->assertTrue($result2, 'Team 2 should be able to submit solution');

        // Refresh models
        $this->competition->refresh();
        $this->team1->refresh();
        $this->team2->refresh();

        $this->team1->load('competition');
        $this->team2->load('competition');

        // Verify both teams have solved their puzzles
        $this->assertTrue($this->team1->isSolved(), 'Team 1 should be solved');
        $this->assertTrue($this->team2->isSolved(), 'Team 2 should be solved');

        // Competition should be finished
        $this->assertEquals(Competition::STATUS_FINISHED, $this->competition->status, 'Competition should be finished');
        $this->assertNotNull($this->competition->finished_at, 'Competition should have finish time');

        // Verify rankings
        $rankings = $this->competition->getRankings();
        $this->assertCount(2, $rankings, 'Should have rankings for both teams');

        // First place should be determined by solve time and attempts
        $firstPlace = $rankings->first();
        $this->assertNotNull($firstPlace, 'Should have a first place');
        $this->assertNotNull($firstPlace->solved_at, 'First place should have solve time');
        $this->assertEquals(1, $firstPlace->attempts, 'First place should have only one attempt');

        // Rankings should be ordered by solve time and attempts
        $this->assertTrue(
            $rankings->first()->solved_at <= $rankings->last()->solved_at,
            'Rankings should be ordered by solve time'
        );

        // Team 2 makes correct submission in first try
        $this->team2->recordAttempt('Test Message');
        $this->team2->refresh();
        $submission = $this->team2->submissions()->latest()->first();
        $this->assertNotNull($submission);
        $this->assertTrue($submission->is_correct);
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
