<?php

namespace Tests\Unit\Models;

use App\Models\Puzzle;
use App\Models\Submission;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_can_be_created_with_factory(): void
    {
        $submission = Submission::factory()->create();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
        ]);
    }

    public function test_submission_belongs_to_team(): void
    {
        $team = Team::factory()->create();
        $submission = Submission::factory()->create(['team_id' => $team->id]);

        $this->assertEquals($team->id, $submission->team->id);
    }

    public function test_submission_belongs_to_puzzle(): void
    {
        $puzzle = Puzzle::factory()->create();
        $submission = Submission::factory()->create(['puzzle_id' => $puzzle->id]);

        $this->assertEquals($puzzle->id, $submission->puzzle->id);
    }

    public function test_submission_can_be_marked_correct(): void
    {
        $submission = Submission::factory()->correct()->create();

        $this->assertTrue($submission->is_correct);
    }

    public function test_submission_can_be_marked_incorrect(): void
    {
        $submission = Submission::factory()->incorrect()->create();

        $this->assertFalse($submission->is_correct);
    }

    public function test_correct_scope_filters_correct_submissions(): void
    {
        Submission::factory()->correct()->count(3)->create();
        Submission::factory()->incorrect()->count(2)->create();

        $correctSubmissions = Submission::correct()->get();

        $this->assertCount(3, $correctSubmissions);
        $this->assertTrue($correctSubmissions->every(fn($s) => $s->is_correct));
    }

    public function test_incorrect_scope_filters_incorrect_submissions(): void
    {
        Submission::factory()->correct()->count(3)->create();
        Submission::factory()->incorrect()->count(2)->create();

        $incorrectSubmissions = Submission::incorrect()->get();

        $this->assertCount(2, $incorrectSubmissions);
        $this->assertTrue($incorrectSubmissions->every(fn($s) => !$s->is_correct));
    }
}
