<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\BandProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BandProfileController extends Controller
{
    public function show()
    {
        $profile = BandProfile::first();

        return response()->json([
            'success' => true,
            'message' => 'Profil band berhasil dimuat.',
            'data' => $profile,
        ]);
    }

    public function update(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'bio' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'formed_year' => 'nullable|string|max:10',
            'social_links' => 'nullable|array',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal.', 'errors' => $v->errors()], 422);
        }

        $profile = BandProfile::firstOrCreate(['name' => 'Neon Horizon']);
        $data = collect($v->validated())->except('logo')->toArray();

        if ($request->hasFile('logo')) {
            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('band_logos', 'public');
        }

        $profile->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil band berhasil diperbarui.',
            'data' => $profile->fresh(),
        ]);
    }
}
