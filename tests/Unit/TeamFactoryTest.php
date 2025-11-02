<?php

namespace Tests\Unit;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_team_with_default_values(): void
    {
        $team = Team::factory()->create();

        $this->assertNotNull($team->display_name);
        $this->assertNotNull($team->slug);
        $this->assertNotNull($team->competition_id);
        $this->assertNull($team->ready_at);
        $this->assertNull($team->solved_at);
        $this->assertEquals(0, $team->attempts);
    }

    public function test_factory_creates_team_with_specific_competition(): void
    {
        $competition = Competition::factory()->create();
        $team = Team::factory()->create([
            'competition_id' => $competition->id,
        ]);

        $this->assertEquals($competition->id, $team->competition_id);
        $this->assertEquals($competition->name, $team->competition->name);
    }

    public function test_factory_can_create_ready_team(): void
    {
        $team = Team::factory()->ready()->create();

        $this->assertNotNull($team->ready_at);
        $this->assertNull($team->solved_at);
    }

    public function test_factory_can_create_solved_team(): void
    {
        $team = Team::factory()->solved()->create();

        $this->assertNotNull($team->ready_at);
        $this->assertNotNull($team->solved_at);
        $this->assertGreaterThan(0, $team->attempts);
    }

    public function test_factory_creates_unique_slugs(): void
    {
        $competition = Competition::factory()->create();

        $team1 = Team::factory()->create([
            'competition_id' => $competition->id,
        ]);
        $team2 = Team::factory()->create([
            'competition_id' => $competition->id,
        ]);

        $this->assertNotEquals($team1->slug, $team2->slug);
    }

    public function test_factory_can_create_multiple_teams(): void
    {
        $competition = Competition::factory()->create();
        $teams = Team::factory()->count(5)->create([
            'competition_id' => $competition->id,
        ]);

        $this->assertCount(5, $teams);
        $this->assertEquals($competition->id, $teams->first()->competition_id);
    }
}
