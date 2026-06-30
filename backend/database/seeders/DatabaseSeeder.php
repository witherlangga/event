<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(BandProfileSeeder::class);
        $this->call(BandMemberSeeder::class);
        $this->call(AlbumSeeder::class);
        $this->call(GallerySeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(EventSeeder::class);
        $this->call(TicketTypeSeeder::class);
        $this->call(CustomerSeeder::class);
    }
}
