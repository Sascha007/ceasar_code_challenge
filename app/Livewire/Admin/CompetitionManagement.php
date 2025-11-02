<?php

namespace App\Livewire\Admin;

use App\Models\Competition;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CompetitionManagement extends Component
{
    public $competitions;

    public $selectedCompetition = null;

    public $showModal = false;

    public $showDeleteModal = false;

    public $competitionToDelete = null;

    #[Validate('required|string|max:255')]
    public $name = '';

    public $status = Competition::STATUS_DRAFT;

    public function mount()
    {
        $this->loadCompetitions();
    }

    public function loadCompetitions()
    {
        $this->competitions = Competition::withCount('teams')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function create()
    {
        $this->resetForm();
        $this->selectedCompetition = null;
        $this->showModal = true;
    }

    public function edit($competitionId)
    {
        $competition = Competition::findOrFail($competitionId);
        $this->selectedCompetition = $competition->id;
        $this->name = $competition->name;
        $this->status = $competition->status;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->selectedCompetition) {
            // Update existing competition
            $competition = Competition::findOrFail($this->selectedCompetition);
            $competition->update([
                'name' => $this->name,
                'status' => $this->status,
            ]);
        } else {
            // Create new competition
            Competition::create([
                'name' => $this->name,
                'status' => $this->status,
            ]);
        }

        $this->loadCompetitions();
        $this->closeModal();
        session()->flash('message', $this->selectedCompetition ? 'Wettbewerb aktualisiert.' : 'Wettbewerb erstellt.');
    }

    public function confirmDelete($competitionId)
    {
        $this->competitionToDelete = Competition::findOrFail($competitionId);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->competitionToDelete) {
            $this->competitionToDelete->delete();
            $this->loadCompetitions();
            $this->showDeleteModal = false;
            $this->competitionToDelete = null;
            session()->flash('message', 'Wettbewerb gelöscht.');
        }
    }

    public function startCompetition($competitionId)
    {
        $competition = Competition::findOrFail($competitionId);
        if ($competition->canStart()) {
            $competition->start();
            $this->loadCompetitions();
            session()->flash('message', 'Wettbewerb gestartet.');
        } else {
            session()->flash('error', 'Wettbewerb kann nicht gestartet werden. Prüfen Sie den Status und ob Teams vorhanden sind.');
        }
    }

    public function finishCompetition($competitionId)
    {
        $competition = Competition::findOrFail($competitionId);
        if ($competition->finish()) {
            $this->loadCompetitions();
            session()->flash('message', 'Wettbewerb beendet.');
        }
    }

    public function resetCompetition($competitionId)
    {
        $competition = Competition::findOrFail($competitionId);
        $competition->reset();
        $this->loadCompetitions();
        session()->flash('message', 'Wettbewerb zurückgesetzt.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->competitionToDelete = null;
    }

    private function resetForm()
    {
        $this->name = '';
        $this->status = Competition::STATUS_DRAFT;
        $this->selectedCompetition = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.competition-management');
    }
}
