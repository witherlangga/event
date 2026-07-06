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
        'moments',
        'band_message',
        'next_show_title',
        'next_show_date',
        'next_show_price_text',
        'next_show_location_name',
        'next_show_location_address',
        'next_show_map_link',
    ];

    protected $casts = [
        'social_links' => 'array',
        'moments' => 'array',
    ];
}
