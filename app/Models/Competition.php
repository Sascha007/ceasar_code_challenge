<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
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

        $this->status = self::STATUS_FINISHED;
        $this->finished_at = now();
        return $this->save();
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
