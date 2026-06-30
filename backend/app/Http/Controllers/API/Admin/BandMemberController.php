<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\BandMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BandMemberController extends Controller
{
    public function index()
    {
        return $this->success(
            BandMember::orderBy('sort_order')->orderBy('name')->get(),
            'Daftar anggota band berhasil dimuat.'
        );
    }

    public function store(Request $request)
    {
        $v = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'role' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('photo')->toArray();
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('band_members', 'public');
        }

        $member = BandMember::create($data);

        return $this->success($member, 'Anggota band berhasil ditambahkan.', 201);
    }

    public function update(Request $request, BandMember $member)
    {
        $v = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'role' => 'nullable|string|max:100',
            'bio' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($v->fails()) {
            return $this->error('Validasi gagal.', 422, $v->errors());
        }

        $data = collect($v->validated())->except('photo')->toArray();
        if ($request->hasFile('photo')) {
            if ($member->photo_path) {
                Storage::disk('public')->delete($member->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('band_members', 'public');
        }

        $member->update($data);

        return $this->success($member->fresh(), 'Anggota band berhasil diperbarui.');
    }

    public function destroy(BandMember $member)
    {
        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }
        $member->delete();

        return $this->success(null, 'Anggota band berhasil dihapus.');
    }
}
