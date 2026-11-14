@extends('layouts.public')
@section('title', 'Profil Saya')

@section('body')
<div class="container">
  <nav class="crumbs">
    <a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Profil</span>
  </nav>

  {{-- Kartu identitas --}}
  <div class="card card-pad" style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;margin-bottom:24px">
    @if($user->avatar)
      <img src="{{ $user->avatar }}" alt="" referrerpolicy="no-referrer"
           style="width:76px;height:76px;border-radius:50%;object-fit:cover;border:3px solid var(--leaf-soft)">
    @else
      <span style="width:76px;height:76px;border-radius:50%;background:var(--forest);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;flex:0 0 auto">
        {{ collect(explode(' ', $user->name))->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('') }}
      </span>
    @endif
    <div class="grow" style="min-width:200px">
      <h1 class="h2" style="font-size:26px;line-height:34px">{{ $user->name }}</h1>
      <p class="small mt-8">{{ $user->email }}</p>
      <div class="row gap-8 mt-8" style="flex-wrap:wrap">
        <span class="badge {{ $user->isAdmin() ? 'badge-solid' : 'badge-green' }}">
          <span class="dot"></span>{{ $user->isAdmin() ? 'Admin' : 'Donatur' }}</span>
        @if($user->username)
          <span class="badge badge-slate">@ {{ $user->username }}</span>
        @endif
        @if($user->google_id)
          <span class="badge badge-slate">Terhubung Google</span>
        @endif
        <span class="badge badge-slate">Bergabung {{ $user->created_at->translatedFormat('M Y') }}</span>
      </div>
    </div>
    <div class="row gap-12" style="flex-wrap:wrap">
      @if($user->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">Panel Admin</a>
      @endif
      <form method="post" action="{{ route('logout') }}">@csrf
        <button class="btn btn-danger btn-sm" onclick="return confirm('Keluar dari akun ini?')">
          <span data-icon="log-out" data-ic-class="ic-sm"></span>Keluar</button>
      </form>
    </div>
  </div>

  {{-- Ringkasan dampak --}}
  <div class="stat-strip" style="margin-bottom:28px">
    <div class="stat-mini"><b class="num">{{ number_format($ringkas['pohon'], 0, ',', '.') }}</b><span>Pohon lunas</span></div>
    <div class="stat-mini"><b class="num">Rp {{ number_format($ringkas['rupiah'], 0, ',', '.') }}</b><span>Donasi lunas</span></div>
    <div class="stat-mini"><b class="num">{{ $ringkas['kampanye'] }}</b><span>Kampanye didukung</span></div>
  </div>

  {{-- Tracking donasi --}}
  <div class="row between mb-16" style="flex-wrap:wrap;gap:12px">
    <div>
      <span class="eyebrow">Tracking Donasi</span>
      <h2 class="h3 mt-8">Progres donasi kamu</h2>
    </div>
    @if($ringkas['pending'])
      <span class="badge badge-amber"><span class="dot"></span>{{ $ringkas['pending'] }} menunggu pembayaran</span>
    @endif
  </div>

  @forelse($donasi as $d)
    @php
      $terkumpul = (int) $d->kampanye->donasi()->where('status','terverifikasi')->sum('jumlah_pohon');
      $target = max(1, $d->kampanye->target_pohon);
      $persen = (int) round(min(100, $terkumpul / $target * 100));
      $logTerakhir = $d->kampanye->logs->first();
    @endphp
    <div class="card" style="padding:20px;margin-bottom:14px">
      <div class="row between gap-16" style="align-items:flex-start;flex-wrap:wrap">
        <div class="grow" style="min-width:240px">
          <div class="row gap-8" style="flex-wrap:wrap;margin-bottom:6px">
            <span class="badge {{ $d->status_badge }}"><span class="dot"></span>{{ $d->status_label }}</span>
            <span class="tiny">{{ $d->created_at->translatedFormat('d M Y') }}</span>
            <span class="tiny mono">#{{ $d->kode }}</span>
          </div>
          <a href="{{ route('katalog.show', $d->kampanye) }}" class="h5" style="display:block">{{ $d->kampanye->judul }}</a>
          <p class="small mt-4" style="margin-top:4px">
            {{ $d->jumlah_pohon }} pohon &middot; Rp {{ number_format($d->total, 0, ',', '.') }}
            &middot; {{ $d->kampanye->lokasi }}
          </p>

          {{-- progres kampanye --}}
          <div class="progress" style="margin:14px 0 8px"><i style="width:{{ $persen }}%"></i></div>
          <div class="row between" style="font-size:12px">
            <b class="num">{{ number_format($terkumpul, 0, ',', '.') }} / {{ number_format($target, 0, ',', '.') }} pohon</b>
            <span class="num muted" style="font-weight:600">{{ $persen }}%</span>
          </div>

          {{-- log terbaru --}}
          @if($logTerakhir)
            <div class="note-pill mt-12" style="background:#F8FAFC;color:var(--text);font-weight:500">
              <span data-icon="sprout" data-ic-class="ic-sm" style="color:var(--emerald)"></span>
              <span>
                <b>{{ $logTerakhir->judul }}</b>
                <span class="tiny" style="display:block">{{ $logTerakhir->tanggal->translatedFormat('d M Y') }} &middot; {{ $logTerakhir->kondisi_label }}</span>
              </span>
            </div>
          @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;flex:0 0 auto;min-width:150px">
          @if($d->status === 'pending')
            <a href="{{ route('donasi.invoice', $d->kode) }}" class="btn btn-primary btn-sm">Bayar sekarang</a>
            <a href="{{ route('profil.donasi', $d) }}" class="btn btn-outline btn-sm">Lihat detail</a>
          @else
            <a href="{{ route('profil.donasi', $d) }}" class="btn btn-primary btn-sm">Lihat detail</a>
            <a href="{{ route('donasi.invoice', $d->kode) }}" class="btn btn-outline btn-sm">Invoice</a>
          @endif
        </div>
      </div>
    </div>
  @empty
    <div class="card empty">
      <span class="chip"><span data-icon="sprout" data-ic-class="ic-lg"></span></span>
      <h3 class="h4">Belum ada donasi</h3>
      <p class="small">Mulai tanam pohon pertamamu — progresnya akan muncul di sini.</p>
      <a href="{{ route('katalog') }}" class="btn btn-primary btn-sm mt-8">Jelajahi kampanye</a>
    </div>
  @endforelse

  <div style="height:56px"></div>
</div>
@endsection
