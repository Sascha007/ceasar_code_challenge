<?php

namespace Tests\Feature\Admin;

use App\Models\Competition;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Competition $competition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->competition = Competition::factory()->create([
            'name' => 'Test Competition',
            'status' => Competition::STATUS_DRAFT,
        ]);
    }

    public function test_can_create_team(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->set('display_name', 'Test Team')
            ->set('competition_id', $this->competition->id)
            ->call('generateSlug')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('teams', [
            'display_name' => 'Test Team',
            'competition_id' => $this->competition->id,
        ]);
    }

    public function test_can_edit_team(): void
    {
        $team = Team::factory()->create([
            'competition_id' => $this->competition->id,
            'display_name' => 'Original Name',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->call('edit', $team->id)
            ->assertSet('selectedTeam', $team->id)
            ->assertSet('display_name', 'Original Name')
            ->set('display_name', 'Updated Name')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'display_name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_team(): void
    {
        $team = Team::factory()->create([
            'competition_id' => $this->competition->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->call('confirmDelete', $team->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete');

        $this->assertDatabaseMissing('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_validation_requires_display_name(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->set('display_name', '')
            ->set('competition_id', $this->competition->id)
            ->set('slug', 'test-slug-1234')
            ->call('save')
            ->assertHasErrors(['display_name']);
    }

    public function test_validation_requires_competition(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->set('display_name', 'Test Team')
            ->set('competition_id', '')
            ->set('slug', 'test-slug-1234')
            ->call('save')
            ->assertHasErrors(['competition_id']);
    }

    public function test_validation_requires_unique_slug(): void
    {
        Team::factory()->create([
            'competition_id' => $this->competition->id,
            'slug' => 'existing-slug',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->set('display_name', 'Test Team')
            ->set('competition_id', $this->competition->id)
            ->set('slug', 'existing-slug')
            ->call('save')
            ->assertHasErrors(['slug']);
    }

    public function test_can_generate_slug_from_display_name(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->set('display_name', 'Test Team Name')
            ->call('generateSlug')
            ->assertSet('slug', function ($slug) {
                return str_starts_with($slug, 'test-team-name-');
            });
    }

    public function test_teams_list_displays_correctly(): void
    {
        Team::factory()->count(3)->create([
            'competition_id' => $this->competition->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\TeamManagement::class)
            ->assertSee($this->competition->name)
            ->assertCount('teams', 3);
    }
}
