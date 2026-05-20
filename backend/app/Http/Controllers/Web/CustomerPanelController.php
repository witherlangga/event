<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\URL;

class CustomerPanelController extends Controller
{
    public function index()
    {
        $events = Event::where('is_active', true)->orderBy('starts_at')->get();
        return view('customer.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $ticketTypes = TicketType::where('event_id', $event->id)->get();
        return view('customer.events.show', compact('event','ticketTypes'));
    }

    public function purchase(Request $request, Event $event)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('organizer.login')->with('error', 'Pilih user untuk impersonate');
        }

        $data = $request->validate([
            'ticket_type_id' => 'required|integer|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $ticketType = TicketType::where('id', $data['ticket_type_id'])->lockForUpdate()->first();
        if (! $ticketType || $ticketType->event_id !== $event->id) {
            return redirect()->back()->with('error','Ticket type not found');
        }

        $qty = (int) $data['quantity'];

        // Create a pending order and redirect to a mock payment page (no real money)
        return DB::transaction(function () use ($user, $event, $ticketType, $qty) {
            $tt = TicketType::where('id', $ticketType->id)->lockForUpdate()->first();
            if (! is_null($tt->quota)) {
                $available = $tt->quota - $tt->sold;
                if ($available < $qty) {
                    return redirect()->back()->with('error', 'Not enough quota');
                }
            }

            // reserve quota but do not increment sold until payment completed

            $order = Order::create([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'total_price' => $tt->price * $qty,
                'status' => 'pending',
            ]);

            $item = OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $tt->id,
                'quantity' => $qty,
                'unit_price' => $tt->price,
                'line_total' => $tt->price * $qty,
            ]);

            // Redirect to mock payment page for this order
            return redirect()->route('customer.mockpay.show', ['order' => $order->id]);
        });
    }

    // Show mock payment page (simulate payment without real money)
    public function mockPaymentShow($orderId)
    {
        $user = Auth::user();
        $order = Order::with(['items','refunds.requester','refunds.processor'])->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        return view('customer.mockpay.show', compact('order'));
    }

    // Complete mock payment: mark order paid and generate tickets & QR
    public function mockPaymentComplete(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::with('items')->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('customer.orders.show', ['order' => $order->id])->with('error','Order already processed');
        }

        DB::transaction(function () use ($order, $user) {
            $writer = new PngWriter();
            foreach ($order->items as $item) {
                $tt = TicketType::where('id', $item->ticket_type_id)->lockForUpdate()->first();
                // increment sold by quantity
                $qty = $item->quantity;
                if (! is_null($tt->quota)) {
                    $available = $tt->quota - $tt->sold;
                    if ($available < $qty) {
                        throw new \Exception('Not enough quota during payment');
                    }
                }
                $tt->sold += $qty;
                $tt->save();

                for ($i = 0; $i < $qty; $i++) {
                    $code = Str::uuid()->toString();
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'ticket_type_id' => $tt->id,
                        'code' => $code,
                    ]);

                    $qr = new QrCode($code);
                    $result = $writer->write($qr);
                    $pngData = $result->getString();

                    $path = 'private/tickets/' . $code . '.png';
                    Storage::disk('local')->put($path, $pngData);

                    $ticket->qr_path = $path;
                    $ticket->save();
                }
            }

            $order->status = 'paid';
            $order->save();
        });

        return redirect()->route('customer.orders.show', ['order' => $order->id])->with('success','Payment simulated and tickets generated');
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->with('items')->orderByDesc('created_at')->get();
        return view('customer.orders.index', compact('orders'));
    }

    public function orderShow($orderId)
    {
        $user = Auth::user();
        $order = Order::with('items')->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        $tickets = Ticket::where('order_id', $order->id)->get();
        foreach ($tickets as $t) {
            $t->signed_download = URL::temporarySignedRoute('tickets.qr.download', now()->addMinutes(30), ['ticket' => $t->id]);
        }

        // pass refunds to view via $order->refunds
        return view('customer.orders.show', compact('order','tickets'));
    }

    // Customer requests refund for specific tickets
    public function requestRefund(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = Order::with('items')->findOrFail($orderId);
        if ($order->user_id !== $user->id) {
            return redirect()->route('customer.orders')->with('error','Unauthorized');
        }

        $data = $request->validate([
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'integer|exists:tickets,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $ticketIds = $data['ticket_ids'];
        $tickets = Ticket::whereIn('id', $ticketIds)->where('order_id', $order->id)->get();
        if (count($tickets) !== count($ticketIds)) {
            return redirect()->back()->with('error','Some tickets not found in this order');
        }

        foreach ($tickets as $t) {
            if ($t->used) {
                return redirect()->back()->with('error','Cannot request refund: one or more tickets already used');
            }
        }

        $amount = 0;
        foreach ($tickets as $t) {
            $amount += $t->ticketType->price;
        }

        $refund = \App\Models\Refund::create([
            'order_id' => $order->id,
            'requested_by' => $user->id,
            'amount' => $amount,
            'reason' => $data['reason'] ?? null,
            'status' => 'requested',
            'ticket_ids' => $ticketIds,
        ]);

        return redirect()->route('customer.orders.show', ['order' => $order->id])->with('success','Refund requested');
    }
}
