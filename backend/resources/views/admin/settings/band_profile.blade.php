@extends('layouts.admin')

@section('admin-content')
    <div class="card">
        <div class="card-header">
            <h2 style="margin:0;">Social Links Settings</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.settings.band_profile.update') }}" method="POST">
                @csrf

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Instagram</label>
                    <input type="url" name="instagram" value="{{ old('instagram', $social['instagram'] ?? '') }}" style="width:100%;" placeholder="https://instagram.com/yourpage">
                    @error('instagram')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">YouTube</label>
                    <input type="url" name="youtube" value="{{ old('youtube', $social['youtube'] ?? '') }}" style="width:100%;" placeholder="https://youtube.com/@yourchannel">
                    @error('youtube')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">TikTok</label>
                    <input type="url" name="tiktok" value="{{ old('tiktok', $social['tiktok'] ?? '') }}" style="width:100%;" placeholder="https://tiktok.com/@yourprofile">
                    @error('tiktok')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <hr style="margin:24px 0; border-color:rgba(255,255,255,0.08);">

                <h3 style="margin-top:0;">Next Show Location</h3>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Next Show Title</label>
                    <input type="text" name="next_show_title" value="{{ old('next_show_title', $profile->next_show_title ?? '') }}" style="width:100%;" placeholder="Live in Jakarta">
                    @error('next_show_title')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Next Show Date</label>
                    <input type="text" name="next_show_date" value="{{ old('next_show_date', $profile->next_show_date ?? '') }}" style="width:100%;" placeholder="23 April 2025 • 20:00">
                    @error('next_show_date')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Next Show Price Text</label>
                    <input type="text" name="next_show_price_text" value="{{ old('next_show_price_text', $profile->next_show_price_text ?? '') }}" style="width:100%;" placeholder="From Rp 500K">
                    @error('next_show_price_text')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Location Name</label>
                    <input type="text" name="next_show_location_name" value="{{ old('next_show_location_name', $profile->next_show_location_name ?? '') }}" style="width:100%;" placeholder="Stadion Gelora Bung Karno">
                    @error('next_show_location_name')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Location Address</label>
                    <textarea name="next_show_location_address" style="width:100%; min-height:100px;" placeholder="Jl. Pintu Satu Senayan, Jakarta">{{ old('next_show_location_address', $profile->next_show_location_address ?? '') }}</textarea>
                    @error('next_show_location_address')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Map Link</label>
                    <input type="url" name="next_show_map_link" value="{{ old('next_show_map_link', $profile->next_show_map_link ?? '') }}" style="width:100%;" placeholder="https://maps.google.com/?q=Jakarta">
                    @error('next_show_map_link')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary" type="submit">Save Links</button>
            </form>
        </div>
    </div>
@endsection
