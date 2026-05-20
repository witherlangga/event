<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    // Public list of active events
    public function index(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = $request->query('radius_km', 10);

        if ($lat !== null && $lng !== null) {
            // If DB supports trig functions (MySQL), do Haversine in SQL for efficiency.
            // For SQLite (used in tests), fallback to PHP calculation because SQLite often lacks trig functions.
            try {
                $driver = DB::getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);
            } catch (\Exception $e) {
                $driver = null;
            }

            if ($driver !== 'sqlite') {
                // Haversine formula to calculate distance in kilometers
                $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat))))";
                $events = Event::selectRaw('*, ' . $haversine . ' AS distance_km', [$lat, $lng, $lat])
                    ->where('is_active', true)
                    ->having('distance_km', '<=', (float) $radius)
                    ->orderBy('distance_km')
                    ->get();
                return response()->json($events);
            }

            // SQLite or unknown driver: calculate distances in PHP
            $events = Event::where('is_active', true)->get()->map(function ($event) use ($lat, $lng) {
                $event->distance_km = $this->haversineDistance((float) $lat, (float) $lng, (float) $event->location_lat, (float) $event->location_lng);
                return $event;
            })->filter(function ($event) use ($radius) {
                return $event->distance_km <= (float) $radius;
            })->sortBy('distance_km')->values();

            return response()->json($events);
        }

        $events = Event::where('is_active', true)->get();
        return response()->json($events);
    }

    // Public event detail (include ticket types and images)
    public function show(Request $request, Event $event)
    {
        $event->load(['ticketTypes', 'images']);
        return response()->json(['event' => $event, 'ticket_types' => $event->ticketTypes, 'images' => $event->images]);
    }

    private function haversineDistance(float $lat1, float $lon1, ?float $lat2, ?float $lon2): float
    {
        if ($lat2 === null || $lon2 === null) return INF;

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
