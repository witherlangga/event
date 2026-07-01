<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event_id', 'total_price', 'status', 'payment_qr_data', 'payment_deadline', 'paid_at'];

    protected $casts = [
        'paid_at' => 'datetime',
        'payment_deadline' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
