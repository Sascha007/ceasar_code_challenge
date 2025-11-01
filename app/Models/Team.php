<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    protected $fillable = [
        'competition_id',
        'slug',
        'display_name',
        'ready_at',
        'solved_at',
        'attempts',
    ];

    protected $casts = [
        'ready_at' => 'datetime',
        'solved_at' => 'datetime',
        'attempts' => 'integer',
    ];

    /**
     * Get the competition this team belongs to.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the puzzle assigned to this team.
     */
    public function puzzle(): HasOne
    {
        return $this->hasOne(Puzzle::class, 'team_id');
    }

    /**
     * Get all submissions by this team.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Determine if the team is ready to start.
     */
    public function isReady(): bool
    {
        return !is_null($this->ready_at);
    }

    /**
     * Mark the team as ready.
     */
    public function markReady(): bool
    {
        if ($this->isReady() || $this->competition->status !== Competition::STATUS_READY) {
            return false;
        }

        $this->ready_at = now();
        return $this->save();
    }

    /**
     * Record a solution attempt.
     */
    public function recordAttempt(string $submission): bool
    {
        $this->loadMissing(['competition', 'puzzle']);

        if ($this->isSolved()) {
            \Log::debug('Team is already solved');
            return false;
        }

        if ($this->competition->status !== Competition::STATUS_RUNNING) {
            \Log::debug('Competition status is not running: ' . $this->competition->status);
            return false;
        }

        if (!$this->puzzle) {
            \Log::debug('No puzzle found for team');
            return false;
        }

        $isCorrect = $this->puzzle->validateSolution($submission);
        \Log::debug('Solution validation result:', [
            'submission' => $submission,
            'isCorrect' => $isCorrect,
            'plaintext' => $this->puzzle->plaintext
        ]);

        try {
            $submissionModel = $this->submissions()->create([
                'content' => $submission,
                'is_correct' => $isCorrect,
            ]);

            $this->increment('attempts');

            if ($isCorrect) {
                $this->update(['solved_at' => now()]);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to create submission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if the team has solved their puzzle.
     */
    public function isSolved(): bool
    {
        return !is_null($this->solved_at);
    }

    /**
     * Mark the puzzle as solved.
     */
    public function markSolved(): bool
    {
        if ($this->isSolved()) {
            return false;
        }

        $this->solved_at = now();
        return $this->save();
    }
}
