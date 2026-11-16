@extends('layouts.admin')
@section('title', 'Pengaturan Halaman Depan')

@section('body')
<div class="admin-head">
  <div>
    <nav class="crumbs" style="padding:0 0 8px">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a><span class="sep">/</span><span class="current">Pengaturan</span>
    </nav>
    <h1 class="h2" style="font-size:30px;line-height:38px">Pengaturan Halaman Depan</h1>
    <p class="small mt-8">Ganti foto besar di samping tulisan "Tanam Pohon Hari Ini, Warisi Hutan untuk Besok".</p>
  </div>
</div>

<div class="grid-2" style="align-items:start">
  <div class="card">
    <div class="card-head">
      <h2 class="h5">Foto Hero</h2>
      <span class="badge {{ $heroImage ? 'badge-green' : 'badge-slate' }}">
        <span class="dot"></span>{{ $heroImage ? 'Terpasang' : 'Belum ada' }}</span>
    </div>
    <div style="padding:20px">
      <form method="post" action="{{ route('admin.pengaturan.hero') }}" enctype="multipart/form-data">
        @csrf
        <label class="dropzone {{ $heroImage ? 'has-file' : '' }}" style="cursor:pointer" id="heroDrop">
          <input type="file" name="hero" accept="image/png,image/jpeg,image/webp" style="display:none" id="heroInput">
          <img id="heroPreview" alt="" style="display:none;width:100%;height:180px;object-fit:cover;border-radius:10px;border:1px solid var(--border)">
          <span id="heroIcon" style="width:46px;height:46px;border-radius:14px;background:#fff;border:1px dashed var(--leaf);display:flex;align-items:center;justify-content:center;color:var(--emerald)">
            <span data-icon="image" data-ic-class="ic"></span></span>
          <b style="font-size:15px" id="heroTitle">{{ $heroImage ? 'Ganti foto hero' : 'Unggah foto hero' }}</b>
          <span class="small" id="heroSub">PNG, JPG, atau WEBP. Maks 5 MB. Rasio disarankan 4:5 (potret).</span>
        </label>
        @error('hero')<p class="err mt-8">{{ $message }}</p>@enderror

        <div class="row between mt-16" style="flex-wrap:wrap;gap:10px">
          <button class="btn btn-primary">Simpan Foto <span data-icon="check" data-ic-class="ic-sm"></span></button>
        </div>
      </form>

      @if($heroImage)
        <form method="post" action="{{ route('admin.pengaturan.hero.hapus') }}" class="mt-12"
              onsubmit="return confirm('Hapus foto hero dan kembali ke tampilan default?')">
          @csrf @method('DELETE')
          <button class="btn btn-danger btn-sm"><span data-icon="x" data-ic-class="ic-sm"></span>Hapus foto</button>
        </form>
      @endif
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2 class="h5">Pratinjau</h2></div>
    <div style="padding:20px">
      <div class="hero-panel" style="height:300px;border-radius:20px"
        @if($heroImage) data-preview="1" @endif>
        @if($heroImage)
          <img src="{{ asset('storage/hero/hero.jpg') }}?v={{ time() }}" alt="Foto hero"
               style="width:100%;height:100%;object-fit:cover;border-radius:20px">
        @else
          <span class="ring r1"></span><span class="ring r2"></span>
          <span data-icon="tree" data-ic-class="tree"></span>
        @endif
      </div>
      <p class="small mt-16">Beginilah foto tampil di halaman depan, di samping judul utama.</p>
      <a href="{{ route('home') }}" target="_blank" class="btn btn-outline btn-sm mt-8">Lihat halaman depan</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const input = document.getElementById('heroInput');
  const preview = document.getElementById('heroPreview');
  const icon = document.getElementById('heroIcon');
  const title = document.getElementById('heroTitle');
  const sub = document.getElementById('heroSub');
  const drop = document.getElementById('heroDrop');

  input.addEventListener('change', () => {
    const f = input.files[0];
    if (!f) return;
    preview.src = URL.createObjectURL(f);
    preview.style.display = 'block';
    icon.style.display = 'none';
    drop.classList.add('has-file');
    title.textContent = f.name;
    sub.textContent = (f.size / 1024 / 1024).toFixed(1) + ' MB · siap diunggah';
  });
</script>
@endpush
