<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JwtBlacklist extends Model
{
    use HasFactory;

    protected $table = 'jwt_blacklist';

    protected $fillable = [
        'jti',
        'user_id',
        'revoked_at',
        'expires_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
}
