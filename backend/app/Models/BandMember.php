<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BandMember extends Model
{
    protected $fillable = [
        'name', 'role', 'bio', 'photo_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
