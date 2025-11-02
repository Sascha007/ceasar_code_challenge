<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * The possible statuses for a competition.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_READY = 'ready';
    public const STATUS_RUNNING = 'running';
    public const STATUS_FINISHED = 'finished';

    /**
     * Get all teams in this competition.
     */
    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    /**
     * Get all puzzles in this competition.
     */
    public function puzzles(): HasMany
    {
        return $this->hasMany(Puzzle::class);
    }

    /**
     * Determine if the competition can be started.
     */
    public function canStart(): bool
    {
        return $this->status === self::STATUS_READY && $this->teams()->count() > 0;
    }

    /**
     * Start the competition.
     */
    public function start(): bool
    {
        if (!$this->canStart()) {
            return false;
        }

        $started = $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now()
        ]);

        if ($started) {
            $this->refresh();
        }

        return $started;
    }

    /**
     * Finish the competition.
     */
    public function finish(): bool
    {
        if ($this->status !== self::STATUS_RUNNING) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_FINISHED,
            'finished_at' => now()
        ]);
    }

    /**
     * Check if all teams have solved their puzzles.
     */
    public function checkAllTeamsSolved(): bool
    {
        if ($this->status !== self::STATUS_RUNNING) {
            \Log::debug('Competition not running, status: ' . $this->status);
            return false;
        }

        $totalTeams = $this->teams()->count();
        $solvedTeams = $this->teams()->whereNotNull('solved_at')->count();

        \Log::debug('Checking teams solved status', [
            'total' => $totalTeams,
            'solved' => $solvedTeams
        ]);

        $allSolved = ($totalTeams > 0 && $totalTeams === $solvedTeams);

        if ($allSolved) {
            \Log::debug('All teams solved, finishing competition');
            $this->finish();
            $this->refresh();  // Stellen Sie sicher, dass wir den aktuellen Status haben
        }

        return $allSolved;
    }

    /**
     * Get team rankings based on solve time and number of attempts.
     */
    public function getRankings()
    {
        return $this->teams()
            ->whereNotNull('solved_at')
            ->orderBy('solved_at')
            ->orderBy('attempts')
            ->get();
    }

    /**
     * Reset the competition.
     */
    public function reset(): bool
    {
        $this->status = self::STATUS_READY;
        $this->started_at = null;
        $this->finished_at = null;

        $this->teams()->update([
            'ready_at' => null,
            'solved_at' => null,
            'attempts' => 0,
        ]);

        return $this->save();
    }
}
