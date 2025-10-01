<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailFetchLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_source',
        'last_fetch_at',
        'last_message_id',
        'last_message_count',
        'total_fetched',
        'total_stored',
        'total_skipped',
        'last_errors',
    ];

    protected $casts = [
        'last_fetch_at' => 'datetime',
        'last_errors' => 'array',
    ];

    /**
     * Get the latest fetch log for a specific email source
     */
    public static function getLatestForSource(string $emailSource = 'designers_inbox'): ?self
    {
        return static::where('email_source', $emailSource)
            ->orderBy('last_fetch_at', 'desc')
            ->first();
    }

    /**
     * Create or update fetch log for a source
     */
    public static function updateFetchLog(string $emailSource, array $data): self
    {
        return static::updateOrCreate(
            ['email_source' => $emailSource],
            array_merge($data, ['updated_at' => now()])
        );
    }
}
