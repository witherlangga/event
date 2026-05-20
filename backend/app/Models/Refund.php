<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\User;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'processed_by', 'requested_by', 'amount', 'reason', 'status', 'ticket_ids', 'processed_at'];

    protected $casts = [
        'ticket_ids' => 'array',
        'processed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
