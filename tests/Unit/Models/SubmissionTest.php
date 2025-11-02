<?php

namespace Tests\Unit\Models;

use App\Models\Submission;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_belongs_to_team(): void
    {
        $team = Team::factory()->create();
        $submission = Submission::factory()->create(['team_id' => $team->id]);

        $this->assertInstanceOf(Team::class, $submission->team);
        $this->assertEquals($team->id, $submission->team->id);
    }

    public function test_is_correct_is_cast_to_boolean(): void
    {
        $submission = Submission::factory()->create(['is_correct' => true]);

        $this->assertIsBool($submission->is_correct);
        $this->assertTrue($submission->is_correct);
    }

    public function test_scope_correct_filters_only_correct_submissions(): void
    {
        $team = Team::factory()->create();
        Submission::factory()->count(3)->create([
            'team_id' => $team->id,
            'is_correct' => true,
        ]);
        Submission::factory()->count(2)->create([
            'team_id' => $team->id,
            'is_correct' => false,
        ]);

        $correctSubmissions = Submission::correct()->get();

        $this->assertCount(3, $correctSubmissions);
        $this->assertTrue($correctSubmissions->every(fn ($s) => $s->is_correct === true));
    }

    public function test_scope_incorrect_filters_only_incorrect_submissions(): void
    {
        $team = Team::factory()->create();
        Submission::factory()->count(3)->create([
            'team_id' => $team->id,
            'is_correct' => true,
        ]);
        Submission::factory()->count(2)->create([
            'team_id' => $team->id,
            'is_correct' => false,
        ]);

        $incorrectSubmissions = Submission::incorrect()->get();

        $this->assertCount(2, $incorrectSubmissions);
        $this->assertTrue($incorrectSubmissions->every(fn ($s) => $s->is_correct === false));
    }

    public function test_submission_stores_content(): void
    {
        $content = 'This is my answer to the puzzle';
        $submission = Submission::factory()->create(['content' => $content]);

        $this->assertEquals($content, $submission->content);
    }

    public function test_submission_can_be_created_with_factory(): void
    {
        $submission = Submission::factory()->create();

        $this->assertInstanceOf(Submission::class, $submission);
        $this->assertNotNull($submission->team_id);
        $this->assertNotNull($submission->content);
        $this->assertIsBool($submission->is_correct);
    }

    public function test_factory_correct_state_creates_correct_submission(): void
    {
        $submission = Submission::factory()->correct()->create();

        $this->assertTrue($submission->is_correct);
    }

    public function test_factory_incorrect_state_creates_incorrect_submission(): void
    {
        $submission = Submission::factory()->incorrect()->create();

        $this->assertFalse($submission->is_correct);
    }
}
