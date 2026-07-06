<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BandProfile;
use Illuminate\Http\Request;

class BandProfileController extends Controller
{
    public function edit()
    {
        $profile = BandProfile::first();
        $social = $profile->social_links ?? [];

        $social = array_merge([
            'instagram' => 'https://instagram.com/neonhorizon',
            'youtube' => 'https://youtube.com/@neonhorizon',
            'tiktok' => 'https://tiktok.com/@neonhorizon',
        ], $social);

        return view('admin.settings.band_profile', compact('profile', 'social'));
    }

    public function editMoments()
    {
        $profile = BandProfile::first();
        $moments = $profile->moments ?? [];
        $band_message = $profile->band_message ?? '';

        return view('admin.settings.moments', compact('profile', 'moments', 'band_message'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'instagram' => 'nullable|url|max:1024',
            'youtube' => 'nullable|url|max:1024',
            'tiktok' => 'nullable|url|max:1024',
        ]);

        $profile = BandProfile::firstOrCreate(['name' => 'Neon Horizon']);
        $profile->social_links = array_filter([
            'instagram' => $data['instagram'] ?? null,
            'youtube' => $data['youtube'] ?? null,
            'tiktok' => $data['tiktok'] ?? null,
        ]);
        $profile->save();

        return redirect()->route('admin.settings.band_profile')->with('success', 'Social links updated successfully.');
    }

    public function updateMoments(Request $request)
    {
        $v = $request->validate([
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
            'band_message' => 'nullable|string|max:2000',
            'existing' => 'nullable|array',
        ]);

        $profile = BandProfile::firstOrCreate(['name' => 'Neon Horizon']);

        $existing = $request->input('existing', []);
        $moments = $profile->moments ?? [];

        // Keep only existing paths that admin didn't remove
        $moments = array_values(array_intersect($moments, $existing));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                if (! $img->isValid()) continue;
                $path = $img->store('moments', 'public');
                $moments[] = '/storage/' . $path;
            }
        }

        $profile->moments = $moments;
        $profile->band_message = $v['band_message'] ?? null;
        $profile->save();

        return redirect()->route('admin.settings.moments')->with('success', 'Moments updated.');
    }
}
