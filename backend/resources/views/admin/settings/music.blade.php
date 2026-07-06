@extends('layouts.admin')

@section('admin-content')
    <div class="card">
        <div class="card-header">
            <h2 style="margin:0;">Daftar Music</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.settings.music.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Judul Lagu</label>
                    <input type="text" name="title" value="{{ old('title') }}" style="width:100%;" placeholder="Nama lagu">
                    @error('title')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Track Number</label>
                    <input type="number" name="track_number" value="{{ old('track_number') }}" min="1" style="width:100%;" placeholder="Biarkan kosong untuk nomor urut otomatis">
                    @error('track_number')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Upload Audio</label>
                    <input type="file" name="audio_file" accept="audio/*">
                    @error('audio_file')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary" type="submit">Tambah Lagu</button>
            </form>

            <hr style="margin:24px 0; border-color:rgba(255,255,255,0.08);">

            <h3>Daftar Lagu</h3>
            @if($songs->isEmpty())
                <div class="alert alert-info">Belum ada lagu di daftar musik.</div>
            @else
                <table class="admin-table" style="width:100%; margin-top:16px;">
                    <thead>
                        <tr>
                            <th style="text-align:left;">#</th>
                            <th style="text-align:left;">Judul</th>
                            <th style="text-align:left;">Status</th>
                            <th style="text-align:left;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($songs as $song)
                            <tr>
                                <td>{{ $song->track_number }}</td>
                                <td>{{ $song->title }}</td>
                                <td>{{ $song->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <form action="{{ route('admin.settings.music.toggle', ['song' => $song->id]) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        <button class="btn btn-secondary" type="submit">{{ $song->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                    </form>
                                    <form action="{{ route('admin.settings.music.delete', ['song' => $song->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Hapus lagu ini?');">
                                        @csrf
                                        <button class="btn btn-danger" type="submit">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
