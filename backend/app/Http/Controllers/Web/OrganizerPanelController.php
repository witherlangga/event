<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Event;
use App\Models\Refund;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OrganizerPanelController extends Controller
{
    public function showLoginForm()
    {
        $users = User::whereIn('role', ['organizer','system_admin','customer'])->get();
        return view('organizer.login', compact('users'));
    }

    public function impersonate(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        session(['impersonate_user_id' => $request->input('user_id')]);
        return redirect()->route('organizer.dashboard');
    }

    public function dashboard()
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('organizer.login')->with('error', 'Pilih user untuk impersonate');
        }

        $events = Event::where('organizer_id', $user->id)->get();
        return view('organizer.dashboard', compact('user', 'events'));
    }

    // Event CRUD (web)
    public function createEvent()
    {
        return view('organizer.events.form', ['event' => new Event()]);
    }

    public function storeEvent(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:0',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['title','description','starts_at','ends_at','capacity']);
        $data['organizer_id'] = $user->id;

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('event_covers', 'public');
            $data['cover_path'] = $path;
        }

        $event = Event::create($data);
        return redirect()->route('organizer.dashboard')->with('success', 'Event dibuat');
    }

    public function editEvent($id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);
        if ($event->organizer_id !== $user->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }
        return view('organizer.events.form', compact('event'));
    }

    public function updateEvent(Request $request, $id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);
        if ($event->organizer_id !== $user->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'capacity' => 'nullable|integer|min:0',
            'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $event->fill($request->only(['title','description','starts_at','ends_at','capacity']));

        if ($request->hasFile('cover')) {
            $path = $request->file('cover')->store('event_covers', 'public');
            if ($event->cover_path) {
                Storage::disk('public')->delete($event->cover_path);
            }
            $event->cover_path = $path;
        }

        $event->save();
        return redirect()->route('organizer.dashboard')->with('success', 'Event diperbarui');
    }

    public function deleteEvent($id)
    {
        $user = Auth::user();
        $event = Event::findOrFail($id);
        if ($event->organizer_id !== $user->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        if ($event->cover_path) {
            Storage::disk('public')->delete($event->cover_path);
        }
        $event->delete();
        return redirect()->route('organizer.dashboard')->with('success', 'Event dihapus');
    }

    // TicketType CRUD under Event
    public function ticketIndex($eventId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        if ($event->organizer_id !== $user->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }
        $tickets = TicketType::where('event_id', $event->id)->get();
        return view('organizer.tickets.index', compact('event','tickets'));
    }

    public function ticketCreate($eventId)
    {
        $event = Event::findOrFail($eventId);
        return view('organizer.tickets.form', ['event' => $event, 'ticket' => new TicketType()]);
    }

    public function ticketStore(Request $request, $eventId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        if ($event->organizer_id !== $user->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:0',
        ]);

        $t = TicketType::create([
            'event_id' => $event->id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'quota' => $request->input('quota'),
            'sold' => 0,
            'is_active' => true,
        ]);

        return redirect()->route('organizer.tickets', ['eventId' => $event->id])->with('success', 'Ticket type dibuat');
    }

    public function ticketEdit($eventId, $ticketId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        $ticket = TicketType::findOrFail($ticketId);
        if ($event->organizer_id !== $user->id || $ticket->event_id !== $event->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        return view('organizer.tickets.form', compact('event','ticket'));
    }

    public function ticketUpdate(Request $request, $eventId, $ticketId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        $ticket = TicketType::findOrFail($ticketId);
        if ($event->organizer_id !== $user->id || $ticket->event_id !== $event->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quota' => 'required|integer|min:0',
        ]);

        $ticket->fill($request->only(['name','description','price','quota']));
        $ticket->save();

        return redirect()->route('organizer.tickets', ['eventId' => $event->id])->with('success', 'Ticket type diperbarui');
    }

    public function ticketDelete($eventId, $ticketId)
    {
        $user = Auth::user();
        $event = Event::findOrFail($eventId);
        $ticket = TicketType::findOrFail($ticketId);
        if ($event->organizer_id !== $user->id || $ticket->event_id !== $event->id) {
            return redirect()->route('organizer.dashboard')->with('error', 'Unauthorized');
        }

        $ticket->delete();
        return redirect()->route('organizer.tickets', ['eventId' => $event->id])->with('success', 'Ticket type dihapus');
    }

    public function refunds()
    {
        $user = Auth::user();
        $all = Refund::with('order.items.ticketType.event')->get();
        $filtered = $all->filter(function ($r) use ($user) {
            if (! $r->order) return false;
            foreach ($r->order->items as $it) {
                if ($it->ticketType && $it->ticketType->event && $it->ticketType->event->organizer_id == $user->id) {
                    return true;
                }
            }
            return false;
        })->values();

        $refunds = $filtered;
        return view('organizer.refunds', compact('refunds'));
    }

    public function approveRefund(Request $request, $id)
    {
        $user = Auth::user();
        $refund = Refund::findOrFail($id);
        if ($refund->status !== 'requested') {
            return redirect()->back()->with('error', 'Refund tidak dalam status requested');
        }

        $ticketIds = (array) $refund->ticket_ids;
        $tickets = Ticket::whereIn('id', $ticketIds)->get();
        foreach ($tickets as $t) {
            if ($t->used) {
                return redirect()->back()->with('error', 'Salah satu tiket sudah digunakan');
            }
        }

        DB::transaction(function () use ($tickets, $refund, $user) {
            foreach ($tickets as $t) {
                $tt = TicketType::lockForUpdate()->find($t->ticket_type_id);
                if ($tt) {
                    $tt->sold = max(0, $tt->sold - 1);
                    $tt->save();
                }

                if ($t->qr_path && Storage::disk('local')->exists($t->qr_path)) {
                    Storage::disk('local')->delete($t->qr_path);
                }
                $t->delete();
            }

            $refund->processed_by = $user->id;
            $refund->processed_at = now();
            $refund->status = 'processed';
            $refund->save();

            $order = $refund->order;
            $order->total_price = max(0, $order->total_price - $refund->amount);
            $remaining = Ticket::where('order_id', $order->id)->count();
            $order->status = $remaining === 0 ? 'cancelled' : 'partial_refunded';
            $order->save();
        });

        return redirect()->back()->with('success', 'Refund diproses');
    }

    public function rejectRefund(Request $request, $id)
    {
        $user = Auth::user();
        $refund = Refund::findOrFail($id);
        if ($refund->status !== 'requested') {
            return redirect()->back()->with('error', 'Refund tidak dalam status requested');
        }

        $refund->status = 'rejected';
        $refund->processed_by = $user->id;
        $refund->processed_at = now();
        $refund->save();

        return redirect()->back()->with('success', 'Refund ditolak');
    }
}
