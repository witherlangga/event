<?php

namespace Database\Seeders;

use App\Models\BandMember;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BandMemberSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $members = [
            ['name' => 'Alex Rivera', 'role' => 'Vokal', 'bio' => 'Frontman dengan suara khas alternative rock.', 'sort_order' => 1],
            ['name' => 'Maya Chen', 'role' => 'Gitar', 'bio' => 'Lead guitarist dan penulis lagu utama.', 'sort_order' => 2],
            ['name' => 'Rizky Pratama', 'role' => 'Bass', 'bio' => 'Menjaga groove dan fondasi rhythm section.', 'sort_order' => 3],
            ['name' => 'Sofia Laurent', 'role' => 'Drum', 'bio' => 'Energi panggung dan beat yang powerful.', 'sort_order' => 4],
        ];

        foreach ($members as $member) {
            BandMember::updateOrCreate(
                ['name' => $member['name']],
                array_merge($member, ['is_active' => true])
            );
        }
    }
}
