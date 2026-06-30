<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            TicketType::updateOrCreate(
                ['event_id' => $event->id, 'name' => 'Regular'],
                [
                    'description' => 'Tiket reguler konser '.$event->title,
                    'price' => 150000,
                    'quota' => 500,
                    'sold' => 0,
                    'is_active' => true,
                ]
            );

            TicketType::updateOrCreate(
                ['event_id' => $event->id, 'name' => 'VIP'],
                [
                    'description' => 'Tiket VIP dengan akses premium',
                    'price' => 350000,
                    'quota' => 50,
                    'sold' => 0,
                    'is_active' => true,
                ]
            );
        }
    }
}
