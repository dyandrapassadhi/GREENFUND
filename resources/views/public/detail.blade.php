@extends('layouts.public')
@section('title', $kampanye->judul)

@php
  $persen = $kampanye->target_pohon > 0 ? (int) round(min(100, $terkumpul / $kampanye->target_pohon * 100)) : 0;
  $sisa = $kampanye->tenggat ? max(0, (int) now()->startOfDay()->diffInDays($kampanye->tenggat, false)) : null;
@endphp

@section('body')
<div class="container">
  <nav class="crumbs">
    <a href="{{ route('home') }}">Home</a><span class="sep">/</span>
    <a href="{{ route('katalog') }}">Katalog Kampanye</a><span class="sep">/</span>
    <span class="current">{{ $kampanye->judul }}</span>
  </nav>

  <div class="detail-hero">
    <span class="ring" style="width:420px;height:420px;top:-120px;right:-80px"></span>
    <span class="ring" style="width:220px;height:220px;bottom:-70px;left:10%"></span>
    <span data-icon="tree" data-ic-class="tree"></span>
    <div class="badges">
      <span class="badge badge-amber">{{ $kampanye->kategori }}</span>
      <span class="badge badge-white"><span data-icon="map-pin" data-ic-class="ic-sm"></span>{{ $kampanye->lokasi }}</span>
      <span class="badge badge-green" style="background:#fff"><span data-icon="check" data-ic-class="ic-sm"></span>Kampanye Terverifikasi</span>
    </div>
  </div>

  <div class="detail-grid mt-32">
    <div>
      <span class="eyebrow">Kampanye {{ $kampanye->kategori }}</span>
      <h1 class="h1 mt-8" style="font-size:40px;line-height:48px">{{ $kampanye->judul }}</h1>
      <div class="meta-row">
        <span><span data-icon="map-pin" data-ic-class="ic-sm"></span>{{ $kampanye->lokasi }}</span>
        @if($sisa !== null)
          <span><span data-icon="clock" data-ic-class="ic-sm"></span>Berakhir {{ $sisa }} hari lagi</span>
        @endif
        <span><span data-icon="users" data-ic-class="ic-sm"></span>{{ number_format($donatur, 0, ',', '.') }} donatur</span>
      </div>

      <div class="tabs">
        <a href="#deskripsi" class="active">Deskripsi</a>
        <a href="#timeline">Timeline Perkembangan Bibit</a>
        <a href="#donatur">Donatur</a>
      </div>

      <div id="deskripsi" class="card card-pad mb-24">
        <h3 class="h4 mb-8">Tentang kampanye ini</h3>
        <p class="body">{{ $kampanye->deskripsi }}</p>
        <div class="row gap-24 mt-24" style="flex-wrap:wrap">
          <span class="small"><b style="color:var(--text)">Target</b> {{ number_format($kampanye->target_pohon, 0, ',', '.') }} pohon</span>
          <span class="small"><b style="color:var(--text)">Harga</b> Rp {{ number_format($kampanye->harga_per_pohon, 0, ',', '.') }}/pohon</span>
          <span class="small"><b style="color:var(--text)">Status</b> {{ $kampanye->status_label }}</span>
        </div>
      </div>

      <div id="timeline" class="card" style="padding:24px">
        <div class="row between mb-24" style="flex-wrap:wrap;gap:8px">
          <div>
            <h3 class="h4">Log Perkembangan Bibit</h3>
            <p class="small">Diperbarui oleh tim lapangan setiap 2 minggu</p>
          </div>
          <span class="badge badge-slate">{{ $logs = $kampanye->logs->count() }} entri</span>
        </div>

        @if($kampanye->logs->isEmpty())
          <div class="empty">
            <span class="chip"><span data-icon="sprout" data-ic-class="ic-lg"></span></span>
            <h4 class="h5">Belum ada log perkembangan</h4>
            <p class="small">Tim lapangan akan mengunggah log setelah penanaman pertama.</p>
          </div>
        @else
          <div class="timeline">
            @foreach($kampanye->logs as $log)
              <div class="tl-item">
                <div class="tl-head">
                  <span>{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                  <span class="badge {{ $log->kondisi === 'baik' ? 'badge-green' : 'badge-amber' }}">
                    <span class="dot"></span>{{ $log->kondisi_label }}</span>
                </div>
                <h4 class="h5">{{ $log->judul }}</h4>
                <p class="small" style="margin-top:4px">{{ $log->catatan }}</p>
                @if($log->foto)
                  <div class="tl-photos">
                    <div class="tl-photo" style="background-image:url('{{ asset('storage/'.$log->foto) }}');background-size:cover;background-position:center"></div>
                    <div class="tl-photo"><span data-icon="sprout" data-ic-class="ic-lg"></span></div>
                  </div>
                @else
                  <div class="tl-photos">
                    <div class="tl-photo"><span data-icon="sprout" data-ic-class="ic-lg"></span></div>
                    <div class="tl-photo"><span data-icon="image" data-ic-class="ic-lg"></span></div>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif
      </div>

      <div id="donatur" class="card card-pad mt-24">
        <h3 class="h4 mb-16">Donatur terbaru</h3>
        @php $recent = $kampanye->donasi()->where('status','terverifikasi')->latest('id')->take(5)->get(); @endphp
        @forelse($recent as $d)
          <div class="row between" style="padding:10px 0;border-bottom:1px solid #F1F5F9">
            <span class="row gap-12">
              <span class="avatar sm">{{ collect(explode(' ', $d->nama_donatur))->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('') }}</span>
              <span>
                <b style="font-size:14px">{{ $d->nama_donatur }}</b>
                <small class="small" style="display:block;font-size:12px">{{ $d->created_at->diffForHumans() }}</small>
              </span>
            </span>
            <b class="num" style="font-size:13.5px">{{ $d->jumlah_pohon }} pohon</b>
          </div>
        @empty
          <p class="small">Belum ada donatur terverifikasi. Jadi yang pertama!</p>
        @endforelse
      </div>
    </div>

    <aside class="sticky">
      <div class="sum-card">
        <h3 class="h5 mb-16">Terkumpul</h3>
        <div class="progress"><i style="width:{{ $persen }}%"></i></div>
        <div class="row between mt-8">
          <b class="num" style="font-size:14px">{{ number_format($terkumpul, 0, ',', '.') }} / {{ number_format($kampanye->target_pohon, 0, ',', '.') }} pohon</b>
          <b class="num" style="font-size:14px;color:var(--emerald)">{{ $persen }}%</b>
        </div>

        <div class="sum-price mt-16">
          <span>Harga per pohon</span>
          <span class="num">Rp {{ number_format($kampanye->harga_per_pohon, 0, ',', '.') }}</span>
        </div>

        <form method="post" action="{{ route('donasi.store') }}" class="mt-16" id="donasiForm">
          @csrf
          <input type="hidden" name="kampanye_id" value="{{ $kampanye->id }}">
          <div class="field">
            <label class="label">Nama donatur</label>
            <div class="input"><input name="nama_donatur" value="{{ old('nama_donatur', auth()->user()?->name) }}" placeholder="Nama kamu" required></div>
          </div>
          <div class="field mt-12">
            <label class="label">Email</label>
            <div class="input"><input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" placeholder="nama@email.com" required></div>
          </div>
          <div class="field mt-16">
            <label class="label">Jumlah pohon</label>
            <div class="stepper">
              <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
              <input type="number" name="jumlah_pohon" id="qty" value="{{ old('jumlah_pohon', 10) }}" min="1" step="1">
              <button type="button" data-step="1" aria-label="Tambah">+</button>
            </div>
          </div>
          <div class="row between mt-16">
            <span class="label">Total donasi</span>
            <b class="num" id="total" style="font-size:16px;font-weight:800">Rp 250.000</b>
          </div>
          <button class="btn btn-primary btn-block mt-16">Lanjutkan Donasi
            <span data-icon="arrow-right" data-ic-class="ic-sm"></span></button>
        </form>

        <div class="note-pill mt-16">
          <span data-icon="shield" data-ic-class="ic-sm"></span>
          Pembayaran diverifikasi manual maks. 1&times;24 jam
        </div>
      </div>

      <div class="sum-card mt-16">
        <h3 class="h5 mb-16">Diselenggarakan oleh</h3>
        <div class="row gap-12">
          <span class="avatar">YB</span>
          <span>
            <b style="font-size:14px;display:block">{{ $kampanye->penyelenggara }}</b>
            <small class="small" style="font-size:12px">Terverifikasi &middot; {{ \App\Models\Kampanye::count() }} kampanye</small>
          </span>
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const qty = document.getElementById('qty');
  const harga = {{ (int) $kampanye->harga_per_pohon }};
  const totalEl = document.getElementById('total');
  const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
  const sync = () => { totalEl.textContent = fmt(Math.max(1, +qty.value || 1) * harga); };
  document.querySelectorAll('[data-step]').forEach(b => b.addEventListener('click', () => {
    qty.value = Math.max(1, (+qty.value || 1) + (+b.dataset.step));
    sync();
  }));
  qty.addEventListener('input', sync);
  sync();
</script>
@endpush
