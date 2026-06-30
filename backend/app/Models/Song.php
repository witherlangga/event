<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    protected $fillable = [
        'album_id', 'title', 'duration_seconds', 'streaming_url', 'track_number', 'is_active',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'track_number' => 'integer',
        'is_active' => 'boolean',
    ];

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }
}
