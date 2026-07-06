<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderReviewController extends Controller
{
    public function index(Request $request)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $tickets = Ticket::with(['order.user', 'order.event', 'ticketType'])
            ->orderByDesc('created_at')
            ->paginate(40);

        $totalOrders = Order::count();
        $totalPaidOrders = Order::where('status', 'paid')->count();
        $totalPendingOrders = Order::where('status', 'pending')->count();
        $totalRevenue = Order::where('status', 'paid')->sum('total_price');
        $totalTickets = Ticket::count();
        $totalBuyers = Order::distinct('user_id')->count('user_id');

        return view('admin.reviews.index', compact(
            'tickets',
            'totalOrders',
            'totalPaidOrders',
            'totalPendingOrders',
            'totalRevenue',
            'totalTickets',
            'totalBuyers'
        ));
    }

    public function deleteTicket(Request $request, Ticket $ticket)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        DB::transaction(function () use ($ticket) {
            $ticket->load(['order', 'ticketType', 'orderItem']);

            $order = $ticket->order;
            $price = optional($ticket->ticketType)->price ?? 0;

            if ($ticket->orderItem) {
                $item = $ticket->orderItem;
                $item->quantity = max(0, $item->quantity - 1);
                $item->line_total = max(0, $item->line_total - $price);

                if ($item->quantity <= 0) {
                    $item->delete();
                } else {
                    $item->save();
                }
            }

            if ($order) {
                $remainingTickets = $order->tickets()->count();
                $order->total_price = max(0, $order->total_price - $price);

                if ($remainingTickets <= 1) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }

            if ($ticket->ticketType && $ticket->ticketType->sold > 0) {
                $ticket->ticketType->sold = max(0, $ticket->ticketType->sold - 1);
                $ticket->ticketType->save();
            }

            $ticket->delete();
        });

        return redirect()->route('admin.reviews')->with('success', 'Selected ticket deleted successfully.');
    }

    protected function isAdmin(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin();
    }
}
