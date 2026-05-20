<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id', 'title', 'description', 'location_name', 'location_address',
        'location_lat', 'location_lng', 'starts_at', 'ends_at', 'capacity', 'is_active', 'cover_path',
    ];

    protected $casts = [
        'location_lat' => 'float',
        'location_lng' => 'float',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function images()
    {
        return $this->hasMany(EventImage::class);
    }

    public function ticketTypes()
    {
        return $this->hasMany(\App\Models\TicketType::class);
    }
}
