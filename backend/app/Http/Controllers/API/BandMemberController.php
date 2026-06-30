<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BandMember;

class BandMemberController extends Controller
{
    public function index()
    {
        $members = BandMember::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($members, 'Daftar anggota band berhasil dimuat.');
    }
}
