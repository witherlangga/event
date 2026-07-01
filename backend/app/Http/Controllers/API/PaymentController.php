<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Generate QRIS QR code for an order.
     * QRIS format: 00020126360007ID.CO.MANDIRI01189370010300006154970208NMMID10254285957769520400005303360614Rp_AMOUNT6304XXXX
     */
    public function generateQris(Request $request, Order $order)
    {
        $user = $request->user();

        // Verify order belongs to user
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Only generate if status is pending
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order already paid or cancelled'], 400);
        }

        // Check if payment deadline has passed
        if ($order->payment_deadline && now()->isAfter($order->payment_deadline)) {
            $order->update(['status' => 'cancelled']);
            return response()->json(['message' => 'Payment deadline expired'], 400);
        }

        try {
            // Generate QRIS data with dynamic amount
            $qrisData = $this->generateQrisData($order->total_price);
            
            // Generate QR code PNG
            $writer = new PngWriter();
            $qr = new QrCode($qrisData);
            $result = $writer->write($qr);
            $pngData = $result->getString();

            // Save QR PNG to storage
            $path = 'private/payments/' . $order->id . '.png';
            Storage::disk('local')->put($path, $pngData);

            // Update order with QRIS data and deadline if not set
            if (!$order->payment_qr_data) {
                $order->update([
                    'payment_qr_data' => $qrisData,
                    'payment_deadline' => now()->addHours(24),
                ]);
            }

            // Generate temporary signed URL for download
            $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                'payment.qr.download',
                now()->addMinutes(5),
                ['order' => $order->id]
            );

            return response()->json([
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'qr_url' => $signedUrl,
                'payment_deadline' => $order->payment_deadline,
                'payment_qr_data' => $qrisData,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Failed to generate QR: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download QRIS QR image via signed URL.
     */
    public function downloadQr(Request $request, Order $order)
    {
        if (!$request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $path = 'private/payments/' . $order->id . '.png';
        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'QR not found'], 404);
        }

        $data = Storage::disk('local')->get($path);
        return response($data, 200)->header('Content-Type', 'image/png');
    }

    /**
     * Check payment status of an order.
     * In real implementation, would check with payment gateway.
     */
    public function checkStatus(Request $request, Order $order)
    {
        $user = $request->user();

        // Verify order belongs to user
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if payment deadline has passed
        if ($order->status === 'pending' && $order->payment_deadline && now()->isAfter($order->payment_deadline)) {
            $order->update(['status' => 'cancelled']);
        }

        return response()->json([
            'order_id' => $order->id,
            'status' => $order->status,
            'amount' => $order->total_price,
            'payment_deadline' => $order->payment_deadline,
            'paid_at' => $order->paid_at,
            'remaining_time' => $order->status === 'pending' && $order->payment_deadline
                ? now()->diffInMinutes($order->payment_deadline, false)
                : null,
        ]);
    }

    /**
     * Confirm payment (in real implementation, called by payment gateway webhook).
     */
    public function confirmPayment(Request $request, Order $order)
    {
        $user = $request->user();

        // Verify order belongs to user or is admin
        if ($order->user_id !== $user->id && !(method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Order already processed'], 400);
        }

        $createdTickets = [];

        DB::transaction(function () use ($order, &$createdTickets) {
            // Mark order as paid
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Create ticket instances for each order item
            $order->load('items');
            $writer = new PngWriter();

            foreach ($order->items as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $code = Str::uuid()->toString();
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'code' => $code,
                    ]);

                    try {
                        $qr = new QrCode($code);
                        $result = $writer->write($qr);
                        $pngData = $result->getString();

                        $path = 'private/tickets/' . $code . '.png';
                        Storage::disk('local')->put($path, $pngData);

                        $ticket->qr_path = $path;
                        $ticket->save();
                    } catch (\Throwable $e) {
                        $ticket->qr_path = null;
                        $ticket->save();
                    }

                    $createdTickets[] = $ticket;
                }
            }
        });

        return response()->json([
            'message' => 'Payment confirmed',
            'order' => $order->fresh(),
            'tickets' => $createdTickets,
        ]);
    }

    /**
     * Generate QRIS string based on amount.
     * Format: Standard QRIS Indonesia
     * This is a simplified version - in production use proper QRIS library
     */
    private function generateQrisData($amount)
    {
        // QRIS Header
        $qrisData = '00020126360007ID.CO.MANDIRI';
        
        // Merchant ID (NMID)
        $qrisData .= '01189370010300006154970208NMMID10254285957769';
        
        // Amount (format: 5203 + amount in cents)
        $amountCents = (int)($amount * 100);
        $qrisData .= '5204' . str_pad($amountCents, 4, '0', STR_PAD_LEFT);
        
        // Country code
        $qrisData .= '5303360';
        
        // CRC (simplified - use proper calculation in production)
        $qrisData .= '0';
        
        return $qrisData;
    }
}
