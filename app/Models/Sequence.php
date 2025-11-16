<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sequence extends Model
{
    use HasFactory;

    protected $table = 'sequences';

    protected $fillable = [
        'entity_type',
        'current_number',
        'prefix',
        'min_digits',
    ];

    protected $casts = [
        'current_number' => 'integer',
        'min_digits' => 'integer',
    ];

    /**
     * Get the next sequential code for an entity type
     * Uses pessimistic locking to prevent race conditions in concurrent environments
     */
    public function getNextCode(): string
    {
        // Use a transaction with row-level locking (FOR UPDATE) to ensure atomicity
        // This prevents multiple processes from getting the same sequential number
        $sequence = DB::transaction(function () {
            $seq = self::lockForUpdate()->find($this->id);

            // Increment the current number within the transaction
            $seq->increment('current_number');

            // Refresh to get the updated value
            $seq->refresh();

            return $seq;
        });

        // Format the code with prefix and padding
        $paddedNumber = str_pad(
            (string) $sequence->current_number,
            $sequence->min_digits,
            '0',
            STR_PAD_LEFT
        );

        return $sequence->prefix . $paddedNumber;
    }

    /**
     * Get or create sequence for an entity type
     * Uses locking during creation to prevent duplicate sequence records
     */
    public static function getSequence(string $entityType): self
    {
        // Use transaction with lock to ensure only one sequence is created per entity type
        return DB::transaction(function () use ($entityType) {
            // Check if sequence exists with read lock first
            $sequence = self::where('entity_type', $entityType)->lockForUpdate()->first();

            if ($sequence) {
                return $sequence;
            }

            // Create new sequence if it doesn't exist
            return self::create([
                'entity_type' => $entityType,
                'current_number' => 0,
                'prefix' => '',
                'min_digits' => 4,
            ]);
        });
    }
}
