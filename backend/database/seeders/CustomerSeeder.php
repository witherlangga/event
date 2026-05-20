<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate([
            'email' => 'customer@example.com',
        ], [
            'name' => 'Default Customer',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}
