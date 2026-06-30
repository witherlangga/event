<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends Controller
{
    public function index(Event $event)
    {
        $tickets = TicketType::where('event_id', $event->id)->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tipe tiket berhasil dimuat.',
            'data' => $tickets,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $data['event_id'] = $event->id;
        $data['sold'] = 0;

        $ticket = TicketType::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tipe tiket berhasil dibuat.',
            'data' => $ticket,
        ], 201);
    }

    public function show(Event $event, TicketType $ticket)
    {
        $this->ensureBelongsToEvent($ticket, $event);

        return response()->json([
            'success' => true,
            'message' => 'Detail tipe tiket berhasil dimuat.',
            'data' => $ticket,
        ]);
    }

    public function update(Request $request, Event $event, TicketType $ticket)
    {
        $this->ensureBelongsToEvent($ticket, $event);

        $v = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        if (isset($data['quota']) && $data['quota'] < $ticket->sold) {
            return response()->json(['success' => false, 'message' => 'Kuota tidak boleh kurang dari tiket terjual.'], 422);
        }

        $ticket->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tipe tiket berhasil diperbarui.',
            'data' => $ticket,
        ]);
    }

    public function destroy(Event $event, TicketType $ticket)
    {
        $this->ensureBelongsToEvent($ticket, $event);
        $ticket->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipe tiket berhasil dihapus.',
            'data' => null,
        ]);
    }

    protected function ensureBelongsToEvent(TicketType $ticket, Event $event): void
    {
        if ($ticket->event_id !== $event->id) {
            abort(404, 'Ticket not found for this event');
        }
    }
}
