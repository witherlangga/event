<?php

namespace App\Http\Controllers\API\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Support\Facades\Validator;

class TicketTypeController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        $tickets = TicketType::where('event_id', $event->id)->get();
        return response()->json($tickets);
    }

    public function store(Request $request, Event $event)
    {
        $this->authorizeOwnership($request->user()->id, $event);

        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quota' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        $data['event_id'] = $event->id;
        $data['sold'] = 0;

        $ticket = TicketType::create($data);
        return response()->json($ticket, 201);
    }

    public function show(Request $request, Event $event, TicketType $ticket)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        $this->ensureBelongsToEvent($ticket, $event);
        return response()->json($ticket);
    }

    public function update(Request $request, Event $event, TicketType $ticket)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        $this->ensureBelongsToEvent($ticket, $event);

        $v = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        $data = $v->validated();

        if (isset($data['quota']) && $data['quota'] < $ticket->sold) {
            return response()->json(['message' => 'Quota cannot be less than already sold tickets'], 422);
        }

        $ticket->update($data);
        return response()->json($ticket);
    }

    public function destroy(Request $request, Event $event, TicketType $ticket)
    {
        $this->authorizeOwnership($request->user()->id, $event);
        $this->ensureBelongsToEvent($ticket, $event);
        $ticket->delete();
        return response()->json(['message' => 'Ticket type deleted']);
    }

    protected function authorizeOwnership(int $userId, Event $event)
    {
        if ($event->organizer_id !== $userId) {
            abort(403, 'Unauthorized');
        }
    }

    protected function ensureBelongsToEvent(TicketType $ticket, Event $event)
    {
        if ($ticket->event_id !== $event->id) {
            abort(404, 'Ticket not found for this event');
        }
    }
}
