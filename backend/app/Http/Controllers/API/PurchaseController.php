<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;

class PurchaseController extends Controller
{
    /**
     * Purchase a ticket type for an event.
     * Accepts: { ticket_type_id, quantity }
     */
    public function purchase(Request $request, Event $event)
    {
        $v = Validator::make($request->all(), [
            'ticket_type_id' => 'required|integer|exists:ticket_types,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $ticketType = TicketType::where('id', $request->ticket_type_id)->lockForUpdate()->first();

        if (! $ticketType || $ticketType->event_id !== $event->id) {
            return response()->json(['message' => 'Ticket type not found for this event'], 404);
        }

        $qty = (int) $request->quantity;

        return DB::transaction(function () use ($request, $event, $ticketType, $qty) {
            // Reload with lock inside transaction
            $tt = TicketType::where('id', $ticketType->id)->lockForUpdate()->first();

            if (! is_null($tt->quota)) {
                $available = $tt->quota - $tt->sold;
                if ($available < $qty) {
                    return response()->json(['message' => 'Not enough quota', 'available' => $available], 422);
                }
            }

            // Update sold
            $tt->sold += $qty;
            $tt->save();

            // Create order
            $order = Order::create([
                'user_id' => $request->user()->id,
                'event_id' => $event->id,
                'total_price' => $tt->price * $qty,
                'status' => 'paid',
            ]);

            $item = OrderItem::create([
                'order_id' => $order->id,
                'ticket_type_id' => $tt->id,
                'quantity' => $qty,
                'unit_price' => $tt->price,
                'line_total' => $tt->price * $qty,
            ]);

            // Create ticket instances and generate QR codes if possible
            $tickets = [];
            $writer = new PngWriter();
            for ($i = 0; $i < $qty; $i++) {
                $code = Str::uuid()->toString();
                $ticket = Ticket::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'ticket_type_id' => $tt->id,
                    'code' => $code,
                ]);

                try {
                    // Generate QR PNG containing ticket code (could be a URL or token)
                    $qr = new QrCode($code);
                    $result = $writer->write($qr);
                    $pngData = $result->getString();

                    $path = 'private/tickets/' . $code . '.png';
                    Storage::disk('local')->put($path, $pngData);

                    $ticket->qr_path = $path;
                    $ticket->save();
                } catch (\Throwable $e) {
                    // QR generation may fail if GD/driver is missing; continue without QR file.
                    $ticket->qr_path = null;
                    $ticket->save();
                }

                $tickets[] = $ticket;
            }

            $order->load('items');

            return response()->json(['order' => $order, 'tickets' => $tickets], 201);
        });
    }
}
