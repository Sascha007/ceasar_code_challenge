<?php

namespace App\Models;

use App\Domain\Caesar\CaesarService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puzzle extends Model
{
    use HasFactory;
    /**
     * Difficulty levels
     */
    public const DIFFICULTY_EASY = 'easy';
    public const DIFFICULTY_MEDIUM = 'medium';
    public const DIFFICULTY_HARD = 'hard';

    protected $fillable = [
        'competition_id',
        'team_id',
        'plaintext',
        'ciphertext',
        'shift',
        'difficulty',
        'points',
    ];

    protected $casts = [
        'shift' => 'integer',
        'points' => 'integer',
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
     * Get all submissions for this puzzle.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
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
