<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BandProfile;

class BandProfileController extends Controller
{
    public function show()
    {
        $profile = BandProfile::first();

        if (! $profile) {
            return $this->success(null, 'Profil band belum tersedia.');
        }

        return $this->success($profile, 'Profil band berhasil dimuat.');
    }
}
