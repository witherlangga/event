@extends('layouts.admin')

@section('admin-content')
    <div class="card">
        <div class="card-header">
            <h2 style="margin:0;">Behind The Stage - Moments</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.settings.moments.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Current Images</label>
                    <div id="moment-list" style="display:flex; gap:12px; flex-wrap:wrap;">
                        @foreach($moments as $m)
                            <div class="moment-admin-item" data-image="{{ e($m) }}" style="position:relative; width:220px; height:220px; border-radius:18px; overflow:hidden; border:1px solid rgba(255,255,255,0.16); background:#111;">
                                <img src="{{ $m }}" style="width:100%; height:100%; object-fit:cover; display:block;" alt="moment">
                                <button type="button" class="remove-image-button" style="position:absolute; right:8px; top:8px; border:none; background:rgba(255,0,0,0.85); color:white; padding:6px 10px; border-radius:999px; cursor:pointer;">Remove</button>
                                <input type="hidden" name="existing[]" value="{{ $m }}">
                            </div>
                        @endforeach
                        @if(count($moments) === 0)
                            <div style="color:var(--text-muted);">No images uploaded yet.</div>
                        @endif
                    </div>
                    <p style="margin-top:10px; color:var(--text-muted); font-size:0.95rem;">Klik "Remove" untuk menghapus gambar yang tidak ingin ditampilkan lagi.</p>
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Upload New Images (jpg/png)</label>
                    <input type="file" name="images[]" multiple accept="image/*">
                    @error('images.*')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <div style="margin-bottom:12px;">
                    <label style="display:block; font-weight:600; margin-bottom:6px;">Band Message (bottom text)</label>
                    <textarea name="band_message" style="width:100%; min-height:120px;">{{ old('band_message', $band_message ?? '') }}</textarea>
                    @error('band_message')<div style="color:#ff6b6b;">{{ $message }}</div>@enderror
                </div>

                <button class="btn btn-primary" type="submit">Save Moments</button>
            </form>
        </div>
    </div>

    <script>
        document.querySelectorAll('.remove-image-button').forEach(function(button) {
            button.addEventListener('click', function() {
                var container = button.closest('.moment-admin-item');
                if (!container) {
                    return;
                }
                container.parentNode.removeChild(container);
            });
        });
    </script>
@endsection
