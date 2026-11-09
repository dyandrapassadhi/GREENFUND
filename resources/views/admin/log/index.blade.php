@extends('layouts.admin')
@section('title', 'Kelola Log Perkembangan')

@section('body')
<div class="admin-head">
  <div>
    <h1 class="h2" style="font-size:30px;line-height:38px">Kelola Log Perkembangan</h1>
    <p class="small mt-8">Catat pertumbuhan bibit per kampanye agar donatur bisa memantau dampaknya.</p>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <div class="row gap-8"><span data-icon="trending" data-ic-class="ic-sm" style="color:var(--emerald)"></span><h2 class="h5">Tambah Log Baru</h2></div>
    <span class="small" style="font-size:12px">Diperbarui {{ now()->subDays(2)->diffForHumans() }}</span>
  </div>
  <form method="post" action="{{ route('admin.log.store') }}" enctype="multipart/form-data" style="padding:20px">
    @csrf
    <div class="grid-3" style="gap:14px">
      <div class="field">
        <label class="label">Kampanye</label>
        <div class="input select"><span data-icon="map-pin" data-ic-class="ic-sm" style="color:var(--muted)"></span>
          <select name="kampanye_id" required>
            @foreach($kampanyeList as $k)
              <option value="{{ $k->id }}" @selected($selected && $selected->id === $k->id)>{{ $k->judul }}</option>
            @endforeach
          </select></div>
      </div>
      <div class="field">
        <label class="label">Tanggal Log</label>
        <div class="input">
          <input type="date" name="tanggal" value="{{ now()->toDateString() }}" required></div>
      </div>
      <div class="field">
        <label class="label">Kondisi Bibit</label>
        <div class="input select"><span data-icon="sprout" data-ic-class="ic-sm" style="color:var(--muted)"></span>
          <select name="kondisi">
            <option value="baik">Tumbuh Baik</option>
            <option value="perawatan">Perlu Perawatan</option>
          </select></div>
      </div>
    </div>

    <div class="grid-2 mt-16">
      <div class="field">
        <label class="label">Judul Log</label>
        <div class="input"><input name="judul" required placeholder="Penanaman batch #4 — 1.200 bibit"></div>
        <label class="label" style="margin-top:8px">Catatan Perkembangan</label>
        <textarea class="textarea" name="catatan" maxlength="500" rows="4"
          placeholder="Tinggi rata-rata 42 cm. Survival rate 94%..."></textarea>
      </div>
      <div class="field">
        <label class="label">Foto Pertumbuhan</label>
        <label class="dropzone" style="padding:24px;flex:1">
          <input type="file" name="foto" accept="image/*" style="display:none" id="fotoLog">
          <span style="width:42px;height:42px;border-radius:12px;background:#fff;border:1px dashed var(--leaf);display:flex;align-items:center;justify-content:center;color:var(--emerald)">
            <span data-icon="image" data-ic-class="ic"></span></span>
          <b style="font-size:13.5px" id="fotoTitle">Pilih Foto</b>
          <span class="tiny" id="fotoSub">PNG atau JPG, maks. 5 MB</span>
        </label>
      </div>
    </div>

    <div class="row between mt-16" style="justify-content:flex-end;gap:10px">
      <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('fotoLog').click()">
        <span data-icon="image" data-ic-class="ic-sm"></span>Pilih Foto</button>
      <button class="btn btn-primary btn-sm">Simpan Log <span data-icon="check" data-ic-class="ic-sm"></span></button>
    </div>
  </form>
</div>

<div class="card mt-24">
  <div class="card-head">
    <h2 class="h5">Riwayat Log — {{ $selected->judul ?? 'Belum ada kampanye' }}</h2>
    <span class="small" style="font-size:12px">{{ $logs->count() }} entri &middot; terakhir {{ optional($logs->first()?->tanggal)->translatedFormat('d M Y') ?? '—' }}</span>
  </div>
  @if($logs->isEmpty())
    <div class="empty">
      <span class="chip"><span data-icon="activity" data-ic-class="ic-lg"></span></span>
      <h3 class="h5">Belum ada log</h3>
      <p class="small">Tambahkan log pertama di form atas.</p>
    </div>
  @else
    <div class="timeline" style="padding:22px 20px 22px 44px">
      @foreach($logs as $log)
        <div class="tl-item">
          <div class="tl-head">
            <span>{{ $log->tanggal->translatedFormat('d M Y') }} &middot; {{ $log->created_at->format('H:i') }}</span>
            <span class="badge {{ $log->kondisi === 'baik' ? 'badge-green' : 'badge-amber' }}"><span class="dot"></span>{{ $log->kondisi_label }}</span>
          </div>
          <div class="row between gap-16" style="align-items:flex-start">
            <div>
              <b style="font-size:14px">{{ $log->judul }}</b>
              <p class="small" style="margin-top:4px">{{ $log->catatan }}</p>
            </div>
            <form method="post" action="{{ route('admin.log.destroy', $log) }}" onsubmit="return confirm('Hapus log ini?')">
              @csrf @method('DELETE')
              <button class="btn-icon" style="width:30px;height:30px;color:var(--danger)" title="Hapus log">
                <span data-icon="x" data-ic-class="ic-sm"></span></button>
            </form>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
  const fi = document.getElementById('fotoLog');
  fi?.addEventListener('change', () => {
    const f = fi.files[0]; if (!f) return;
    document.getElementById('fotoTitle').textContent = f.name;
    document.getElementById('fotoSub').textContent = (f.size / 1024 / 1024).toFixed(1) + ' MB · siap diunggah';
  });
</script>
@endpush
