<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class TicketController extends Controller
{
    /**
     * Serve QR image for a ticket with access checks.
     */
    public function qr(Request $request, Ticket $ticket)
    {
        $user = $request->user();

        // Allow if system admin
        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            // allowed
        } elseif ($user->id === $ticket->order->user_id) {
            // ticket owner
        } elseif (optional($ticket->ticketType->event)->organizer_id === $user->id) {
            // event organizer
        } else {
            return response()->json(['message' => 'Unauthorized to access this ticket'], 403);
        }

        $path = $ticket->qr_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'QR not found'], 404);
        }

        // Generate temporary signed URL to download endpoint (valid 5 minutes)
        $url = URL::temporarySignedRoute(
            'tickets.qr.download',
            now()->addMinutes(5),
            ['ticket' => $ticket->id]
        );

        return response()->json(['url' => $url]);
    }

    /**
     * Download QR image via signed URL (no JWT required, signature protects access)
     */
    public function download(Request $request, Ticket $ticket)
    {
        if (! $request->hasValidSignature()) {
            return response()->json(['message' => 'Invalid or expired link'], 403);
        }

        $path = $ticket->qr_path;
        if (! $path || ! Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'QR not found'], 404);
        }

        $data = Storage::disk('local')->get($path);
        return response($data, 200)->header('Content-Type', 'image/png');
    }
}
