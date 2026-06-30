<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Ticket;
use App\Models\TicketType;

class OrderController extends Controller
{
    // List orders (admin sees all, fan sees own orders)
    public function index(Request $request)
    {
        $user = $request->user();
        $q = Order::with('items.ticketType', 'tickets', 'user', 'event');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('event_id')) {
            $q->where('event_id', $request->event_id);
        }
        if ($request->filled('q')) {
            $q->whereHas('user', function ($q2) use ($request) {
                $q2->where('email', 'like', '%' . $request->q . '%')
                   ->orWhere('name', 'like', '%' . $request->q . '%');
            });
        }

        if (! (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            $q->where('user_id', $user->id);
        }

        $orders = $q->orderByDesc('created_at')->paginate(20);
        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        $user = $request->user();
        $order->load('items.ticketType', 'tickets', 'user', 'event', 'items');

        $isAdmin = method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin();
        if (! $isAdmin && $order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($order);
    }

    // Legacy immediate refund - kept for admin quick refunds
    public function refund(Request $request, Order $order)
    {
        $user = $request->user();
        if (! (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Perform full immediate refund (admin)
        $order->load('items', 'items.ticketType');
        $tickets = Ticket::where('order_id', $order->id)->get();

        foreach ($tickets as $t) {
            if ($t->used) {
                return response()->json(['message' => 'Cannot refund: one or more tickets already used'], 422);
            }
        }

        return DB::transaction(function () use ($request, $order, $user, $tickets) {
            $amount = $order->total_price;
            $refund = Refund::create([
                'order_id' => $order->id,
                'processed_by' => $user->id,
                'amount' => $amount,
                'reason' => $request->input('reason'),
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            foreach ($order->items as $item) {
                $tt = TicketType::lockForUpdate()->find($item->ticket_type_id);
                if ($tt) {
                    $tt->sold = max(0, $tt->sold - $item->quantity);
                    $tt->save();
                }
            }

            foreach ($tickets as $t) {
                if ($t->qr_path && Storage::disk('local')->exists($t->qr_path)) {
                    Storage::disk('local')->delete($t->qr_path);
                }
                $t->delete();
            }

            $order->status = 'cancelled';
            $order->save();

            return response()->json(['refund' => $refund]);
        });
    }

    // Customer requests a refund for specific tickets (partial refunds)
    public function requestRefund(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id !== $user->id && ! (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
            'reason' => 'nullable|string',
        ]);

        $ticketIds = $data['ticket_ids'];
        $tickets = Ticket::whereIn('id', $ticketIds)->where('order_id', $order->id)->get();
        if (count($tickets) !== count($ticketIds)) {
            return response()->json(['message' => 'Some tickets not found in this order'], 422);
        }

        foreach ($tickets as $t) {
            if ($t->used) {
                return response()->json(['message' => 'Cannot request refund: one or more tickets already used'], 422);
            }
        }

        // compute amount from ticket types
        $amount = 0;
        foreach ($tickets as $t) {
            $amount += $t->ticketType->price;
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'requested_by' => $user->id,
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
            'status' => 'requested',
            'ticket_ids' => $ticketIds,
        ]);

        return response()->json(['refund' => $refund], 201);
    }

    // Approve and process a requested refund (admin only)
    public function approveRefund(Request $request, Refund $refund)
    {
        $user = $request->user();

        if (! (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($refund->status !== 'requested') {
            return response()->json(['message' => 'Refund not in requested state'], 422);
        }

        $ticketIds = (array) $refund->ticket_ids;
        $tickets = Ticket::whereIn('id', $ticketIds)->get();
        foreach ($tickets as $t) {
            if ($t->used) {
                return response()->json(['message' => 'Cannot process refund: one or more tickets already used'], 422);
            }
        }

        return DB::transaction(function () use ($refund, $tickets, $user) {
            // decrement sold per ticket type
            foreach ($tickets as $t) {
                $tt = TicketType::lockForUpdate()->find($t->ticket_type_id);
                if ($tt) {
                    $tt->sold = max(0, $tt->sold - 1);
                    $tt->save();
                }
            }

            // delete tickets and QR files
            foreach ($tickets as $t) {
                if ($t->qr_path && Storage::disk('local')->exists($t->qr_path)) {
                    Storage::disk('local')->delete($t->qr_path);
                }
                $t->delete();
            }

            // update refund
            $refund->processed_by = $user->id;
            $refund->processed_at = now();
            $refund->status = 'processed';
            $refund->save();

            // update order total and status
            $order = $refund->order;
            $order->total_price = max(0, $order->total_price - $refund->amount);
            // if no tickets remain for this order -> cancelled, else partial_refunded
            $remaining = Ticket::where('order_id', $order->id)->count();
            $order->status = $remaining === 0 ? 'cancelled' : 'partial_refunded';
            $order->save();

            return response()->json(['refund' => $refund]);
        });
    }

    // Reject a refund request (admin only)
    public function rejectRefund(Request $request, Refund $refund)
    {
        $user = $request->user();

        if (! (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($refund->status !== 'requested') {
            return response()->json(['message' => 'Refund not in requested state'], 422);
        }

        $refund->status = 'rejected';
        $refund->processed_by = $user->id;
        $refund->processed_at = now();
        $refund->save();

        return response()->json(['refund' => $refund]);
    }
}
