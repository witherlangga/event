<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $defaultAvatarUrl = asset('images/default-avatar.svg');

        return view('profile.show', compact('user', 'defaultAvatarUrl'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Validasi user sudah login dan merupakan instance User model
        if (!$user || !($user instanceof User)) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:40',
            'bio' => 'nullable|string|max:1000',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // Handle file upload
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // Assign values manually
        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? $user->phone;
        $user->bio = $validated['bio'] ?? $user->bio;
        $user->location_lat = $validated['location_lat'] ?? $user->location_lat;
        $user->location_lng = $validated['location_lng'] ?? $user->location_lng;
        if (isset($validated['profile_photo_path'])) {
            $user->profile_photo_path = $validated['profile_photo_path'];
        }
        $user->save();

        return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        // Validasi user sudah login dan merupakan instance User model
        if (!$user || !($user instanceof User)) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        // Update password directly
        $user->password = $validated['password'];
        $user->save();

        return redirect()->route('profile')->with('success', 'Password berhasil diperbarui.');
    }
}
