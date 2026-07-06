<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $events = Event::with('ticketTypes')->orderByDesc('starts_at')->get();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        return view('admin.events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $data = $this->validatedEventPayload($request);

        DB::transaction(function () use ($request, $data) {
            $event = Event::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location_name' => $data['location_name'],
                'location_address' => $data['location_address'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->hasFile('cover')) {
                $event->cover_path = $request->file('cover')->store('event_covers', 'public');
                $event->save();
            }

            $this->syncTicketTypes($event, $data);
        });

        return redirect()->route('admin.events')->with('success', 'Event and tickets created successfully.');
    }

    public function edit(Event $event)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $event->load('ticketTypes');

        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $data = $this->validatedEventPayload($request);

        DB::transaction(function () use ($request, $event, $data) {
            $event->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'location_name' => $data['location_name'],
                'location_address' => $data['location_address'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'capacity' => $data['capacity'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            if ($request->hasFile('cover')) {
                if ($event->cover_path) {
                    Storage::disk('public')->delete($event->cover_path);
                }
                $event->cover_path = $request->file('cover')->store('event_covers', 'public');
                $event->save();
            }

            $this->syncTicketTypes($event, $data);
        });

        return redirect()->route('admin.events')->with('success', 'Event and tickets updated successfully.');
    }

    public function destroy(Event $event)
    {
        if (! $this->isAdmin()) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        if ($event->cover_path) {
            Storage::disk('public')->delete($event->cover_path);
        }

        $event->delete();

        return redirect()->route('admin.events')->with('success', 'Event deleted successfully.');
    }

    protected function validatedEventPayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string|max:500',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'ends_time' => 'nullable|date_format:H:i',
            'capacity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
            'regular_price' => 'required|numeric|min:0',
            'regular_quota' => 'required|integer|min:1',
            'vip_price' => 'required|numeric|min:0',
            'vip_quota' => 'required|integer|min:1',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $startsAt = $validated['event_date'].' '.$validated['event_time'].':00';
        $endsAt = null;

        if (! empty($validated['ends_time'])) {
            $endsAt = $validated['event_date'].' '.$validated['ends_time'].':00';
        }

        $validated['starts_at'] = $startsAt;
        $validated['ends_at'] = $endsAt;

        return $validated;
    }

    protected function syncTicketTypes(Event $event, array $data): void
    {
        $definitions = [
            'Regular' => [
                'price' => $data['regular_price'],
                'quota' => $data['regular_quota'],
                'description' => 'Regular admission ticket',
            ],
            'VIP' => [
                'price' => $data['vip_price'],
                'quota' => $data['vip_quota'],
                'description' => 'VIP admission ticket with premium access',
            ],
        ];

        foreach ($definitions as $name => $ticketData) {
            $ticket = TicketType::firstOrNew([
                'event_id' => $event->id,
                'name' => $name,
            ]);

            $ticket->fill([
                'description' => $ticketData['description'],
                'price' => $ticketData['price'],
                'quota' => $ticketData['quota'],
                'is_active' => true,
                'sold' => $ticket->sold ?? 0,
            ]);

            $ticket->save();
        }
    }

    protected function isAdmin(): bool
    {
        $user = Auth::user();

        return $user && method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin();
    }
}
