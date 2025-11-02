<?php

namespace App\Livewire\Admin;

use App\Models\Competition;
use App\Models\Team;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

class TeamManagement extends Component
{
    public $teams;

    public $competitions;

    public $selectedTeam = null;

    public $showModal = false;

    public $showDeleteModal = false;

    public $teamToDelete = null;

    #[Validate('required|string|max:255')]
    public $display_name = '';

    #[Validate('required|exists:competitions,id')]
    public $competition_id = '';

    #[Validate('required|string|max:255|unique:teams,slug')]
    public $slug = '';

    public function mount()
    {
        $this->loadTeams();
        $this->loadCompetitions();
    }

    public function loadTeams()
    {
        $this->teams = Team::with('competition')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function loadCompetitions()
    {
        $this->competitions = Competition::all();
    }

    public function create()
    {
        $this->resetForm();
        $this->selectedTeam = null;
        $this->showModal = true;
    }

    public function edit($teamId)
    {
        $team = Team::findOrFail($teamId);
        $this->selectedTeam = $team->id;
        $this->display_name = $team->display_name;
        $this->competition_id = $team->competition_id;
        $this->slug = $team->slug;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->selectedTeam) {
            // Update existing team
            $this->validate([
                'display_name' => 'required|string|max:255',
                'competition_id' => 'required|exists:competitions,id',
                'slug' => 'required|string|max:255|unique:teams,slug,'.$this->selectedTeam,
            ]);

            $team = Team::findOrFail($this->selectedTeam);
            $team->update([
                'display_name' => $this->display_name,
                'competition_id' => $this->competition_id,
                'slug' => $this->slug,
            ]);
        } else {
            // Create new team
            $this->validate();

            Team::create([
                'display_name' => $this->display_name,
                'competition_id' => $this->competition_id,
                'slug' => $this->slug,
            ]);
        }

        $this->loadTeams();
        $this->closeModal();
        session()->flash('message', $this->selectedTeam ? 'Team aktualisiert.' : 'Team erstellt.');
    }

    public function confirmDelete($teamId)
    {
        $this->teamToDelete = Team::findOrFail($teamId);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->teamToDelete) {
            $this->teamToDelete->delete();
            $this->loadTeams();
            $this->showDeleteModal = false;
            $this->teamToDelete = null;
            session()->flash('message', 'Team gelöscht.');
        }
    }

    public function generateSlug()
    {
        if (! empty($this->display_name)) {
            $baseSlug = Str::slug($this->display_name);
            $this->slug = $baseSlug.'-'.rand(1000, 9999);
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->teamToDelete = null;
    }

    private function resetForm()
    {
        $this->display_name = '';
        $this->competition_id = '';
        $this->slug = '';
        $this->selectedTeam = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.team-management');
    }
}
