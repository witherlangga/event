<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandProfile extends Model
{
    protected $fillable = [
        'name',
        'bio',
        'logo_path',
        'genre',
        'formed_year',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];
}
