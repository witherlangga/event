<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'order_item_id', 'ticket_type_id', 'code', 'qr_path', 'used', 'used_at'];

    protected $casts = [
        'used' => 'boolean',
        'used_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }
}
