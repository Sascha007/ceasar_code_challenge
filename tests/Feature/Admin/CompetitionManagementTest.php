<?php

namespace Tests\Feature\Admin;

use App\Models\Competition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompetitionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_can_view_competitions_list(): void
    {
        Competition::factory()->count(3)->create();

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->assertSee('Wettbewerbe verwalten')
            ->assertCount('competitions', 3);
    }

    public function test_can_create_new_competition(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->set('name', 'Test Competition')
            ->set('status', Competition::STATUS_DRAFT)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('competitions', [
            'name' => 'Test Competition',
            'status' => Competition::STATUS_DRAFT,
        ]);
    }

    public function test_can_edit_competition(): void
    {
        $competition = Competition::factory()->create([
            'name' => 'Original Name',
            'status' => Competition::STATUS_DRAFT,
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('edit', $competition->id)
            ->assertSet('showModal', true)
            ->assertSet('name', 'Original Name')
            ->set('name', 'Updated Name')
            ->set('status', Competition::STATUS_READY)
            ->call('save')
            ->assertSet('showModal', false);

        $this->assertDatabaseHas('competitions', [
            'id' => $competition->id,
            'name' => 'Updated Name',
            'status' => Competition::STATUS_READY,
        ]);
    }

    public function test_can_delete_competition(): void
    {
        $competition = Competition::factory()->create([
            'name' => 'To Be Deleted',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('confirmDelete', $competition->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseMissing('competitions', [
            'id' => $competition->id,
        ]);
    }

    public function test_validation_requires_name(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('create')
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    }

    public function test_can_start_ready_competition_with_teams(): void
    {
        $competition = Competition::factory()->ready()->create();
        $competition->teams()->create([
            'slug' => 'team1',
            'display_name' => 'Team One',
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('startCompetition', $competition->id);

        $competition->refresh();
        $this->assertEquals(Competition::STATUS_RUNNING, $competition->status);
        $this->assertNotNull($competition->started_at);
    }

    public function test_can_finish_running_competition(): void
    {
        $competition = Competition::factory()->running()->create();

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('finishCompetition', $competition->id);

        $competition->refresh();
        $this->assertEquals(Competition::STATUS_FINISHED, $competition->status);
        $this->assertNotNull($competition->finished_at);
    }

    public function test_can_reset_finished_competition(): void
    {
        $competition = Competition::factory()->finished()->create();
        $team = $competition->teams()->create([
            'slug' => 'team1',
            'display_name' => 'Team One',
            'solved_at' => now(),
            'attempts' => 3,
        ]);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('resetCompetition', $competition->id);

        $competition->refresh();
        $team->refresh();

        $this->assertEquals(Competition::STATUS_READY, $competition->status);
        $this->assertNull($competition->started_at);
        $this->assertNull($competition->finished_at);
        $this->assertNull($team->solved_at);
        $this->assertEquals(0, $team->attempts);
    }

    public function test_cannot_start_competition_without_teams(): void
    {
        $competition = Competition::factory()->ready()->create();

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('startCompetition', $competition->id);

        $competition->refresh();
        $this->assertEquals(Competition::STATUS_READY, $competition->status);
    }

    public function test_modal_closes_on_cancel(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('create')
            ->assertSet('showModal', true)
            ->call('closeModal')
            ->assertSet('showModal', false)
            ->assertSet('name', '')
            ->assertSet('selectedCompetition', null);
    }

    public function test_delete_modal_closes_on_cancel(): void
    {
        $competition = Competition::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Admin\CompetitionManagement::class)
            ->call('confirmDelete', $competition->id)
            ->assertSet('showDeleteModal', true)
            ->call('closeDeleteModal')
            ->assertSet('showDeleteModal', false)
            ->assertSet('competitionToDelete', null);
    }
}
