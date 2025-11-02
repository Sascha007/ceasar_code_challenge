<?php

namespace App\Models;

use App\Domain\Caesar\CaesarService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Puzzle extends Model
{
    protected $fillable = [
        'competition_id',
        'team_id',
        'plaintext',
        'ciphertext',
        'shift',
    ];

    protected $casts = [
        'shift' => 'integer',
    ];

    /**
     * Get the competition this puzzle belongs to.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * Get the team this puzzle is assigned to.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Generate ciphertext from plaintext using the given shift value.
     */
    public function generateCiphertext(): void
    {
        $caesarService = new CaesarService;
        $this->ciphertext = $caesarService->encode($this->plaintext, $this->shift);
    }

    /**
     * Validate a submitted solution.
     */
    public function validateSolution(string $solution): bool
    {
        // Trim both strings and compare case-insensitive
        return strtolower(trim($solution)) === strtolower(trim($this->plaintext));
    }

    /**
     * Save the model after generating the ciphertext if plaintext changed.
     */
    public function save(array $options = []): bool
    {
        if ($this->isDirty('plaintext') || $this->isDirty('shift')) {
            $this->generateCiphertext();
        }

        return parent::save($options);
    }
}
