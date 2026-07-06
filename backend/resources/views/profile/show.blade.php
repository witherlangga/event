@extends('layouts.panel')

@section('content')
    <div class="section" style="max-width:1100px; margin:auto;">
        <div class="card" style="overflow:hidden;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <h2 style="margin:0;">Profile Saya</h2>
                    <p style="margin:8px 0 0 0; color:var(--text-muted);">Kelola informasi akun, foto, dan keamanan Anda.</p>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:64px; height:64px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg, #7c3aed, #2563eb); display:flex; align-items:center; justify-content:center; border:2px solid rgba(255,255,255,0.16);">
                        @if($user->profile_photo_path)
                            <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <img src="{{ $defaultAvatarUrl }}" alt="Avatar default" style="width:100%; height:100%; object-fit:cover;">
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:700;">{{ $user->name }}</div>
                        <div style="font-size:0.95rem; color:var(--text-muted);">{{ $user->email }}</div>
                    </div>
                </div>
            </div>

            <div class="card-body" style="display:grid; gap:24px;">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
                    <div style="border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:20px; background:rgba(255,255,255,0.03);">
                        <h3 style="margin-top:0; margin-bottom:16px;">Informasi Profil</h3>
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="display:grid; gap:16px;">
                            @csrf

                            <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
                                <div style="width:96px; height:96px; border-radius:24px; overflow:hidden; background:#151522; display:flex; align-items:center; justify-content:center; border:1px solid rgba(255,255,255,0.1);">
                                    @if($user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Foto profil" style="width:100%; height:100%; object-fit:cover;">
                                    @else
                                        <img src="{{ $defaultAvatarUrl }}" alt="Foto profil default" style="width:100%; height:100%; object-fit:cover;">
                                    @endif
                                </div>
                                <div style="flex:1; min-width:220px;">
                                    <label style="display:block; font-weight:600; margin-bottom:6px;">Foto Profil</label>
                                    <input type="file" name="profile_photo" accept="image/*">
                                    @error('profile_photo')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Nama</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" style="width:100%;" required>
                                @error('name')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Email</label>
                                <input type="email" value="{{ $user->email }}" style="width:100%; background:rgba(255,255,255,0.05);" disabled>
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Telepon</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="width:100%;">
                                @error('phone')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Bio</label>
                                <textarea name="bio" style="width:100%; min-height:120px;">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                            </div>

                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                                <div>
                                    <label style="display:block; font-weight:600; margin-bottom:6px;">Latitude</label>
                                    <input type="text" name="location_lat" value="{{ old('location_lat', $user->location_lat) }}" style="width:100%;">
                                    @error('location_lat')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label style="display:block; font-weight:600; margin-bottom:6px;">Longitude</label>
                                    <input type="text" name="location_lng" value="{{ old('location_lng', $user->location_lng) }}" style="width:100%;">
                                    @error('location_lng')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            <button class="btn btn-primary" type="submit">Simpan Profil</button>
                        </form>
                    </div>

                    <div style="border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:20px; background:rgba(255,255,255,0.03);">
                        <h3 style="margin-top:0; margin-bottom:16px;">Keamanan Akun</h3>
                        <p style="margin-top:0; color:var(--text-muted);">Perbarui password Anda secara berkala untuk menjaga keamanan akun.</p>
                        <form action="{{ route('profile.password.update') }}" method="POST" style="display:grid; gap:14px;">
                            @csrf

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Password Saat Ini</label>
                                <input type="password" name="current_password" style="width:100%;" required>
                                @error('current_password')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Password Baru</label>
                                <input type="password" name="password" style="width:100%;" required>
                                @error('password')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                            </div>

                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" style="width:100%;" required>
                            </div>

                            <button class="btn btn-primary" type="submit">Ubah Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
