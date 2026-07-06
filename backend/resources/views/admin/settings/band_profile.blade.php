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

                <button class="btn btn-primary" type="submit">Save Links</button>
            </form>
        </div>
    </div>
@endsection
