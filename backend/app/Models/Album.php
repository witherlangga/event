<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    protected $fillable = [
        'title', 'description', 'cover_path', 'released_at', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'released_at' => 'date',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->orderBy('track_number');
    }
}
