@extends('layouts.admin')
@section('title', 'Dashboard')

@section('body')
<div class="admin-head">
  <div>
    <h1 class="h2" style="font-size:30px;line-height:38px">Dashboard Overview</h1>
    <p class="small mt-8">Ringkasan aktivitas platform &middot; {{ now()->translatedFormat('l, d F Y') }}</p>
  </div>
  <div class="tools">
    <form class="search-box" method="get" action="{{ route('admin.donasi.index') }}">
      <span data-icon="search" data-ic-class="ic-sm"></span>
      <input name="q" placeholder="Cari donatur, ID..." value="{{ request('q') }}">
    </form>
    <button class="btn btn-outline btn-sm"><span data-icon="calendar" data-ic-class="ic-sm"></span>01 – {{ now()->format('d M') }}</button>
    <button class="btn-icon" style="width:40px;height:40px"><span data-icon="bell" data-ic-class="ic-sm"></span></button>
  </div>
</div>

<div class="stat-cards">
  @foreach([
    ['layers', 'chip-green', $stat['kampanye_aktif'], 'Kampanye Aktif', 'green', '+2 bulan ini'],
    ['tree', 'chip-amber', number_format($stat['pohon'], 0, ',', '.'), 'Pohon Tertanam', 'amber', '+1.240'],
    ['clock', 'chip-amber', $stat['pending'], 'Menunggu Verifikasi', 'amber', 'Perlu aksi'],
    ['users', 'chip-green', number_format($stat['donatur'], 0, ',', '.'), 'Total Donatur', 'green', '+86'],
  ] as [$icon, $chip, $value, $label, $dot, $note])
    <div class="stat-card">
      <span class="chip {{ $chip }}"><span data-icon="{{ $icon }}" data-ic-class="ic-sm"></span></span>
      <b class="num">{{ $value }}</b>
      <span class="lbl">{{ $label }}</span>
      <span class="badge badge-{{ $dot }}"><span class="dot"></span>{{ $note }}</span>
    </div>
  @endforeach
</div>

<div class="card mt-24" style="overflow:hidden">
  <div class="card-head">
    <div>
      <h2 class="h5">Donasi Terbaru</h2>
      <p class="small" style="font-size:12px">{{ $donasi->count() }} transaksi terakhir</p>
    </div>
    <div class="row gap-12">
      <a href="{{ route('admin.donasi.index') }}" class="btn btn-outline btn-sm"><span data-icon="filter" data-ic-class="ic-sm"></span>Filter</a>
      <a href="{{ route('admin.donasi.index') }}" class="small" style="color:var(--emerald);font-weight:700">Lihat semua &rsaquo;</a>
    </div>
  </div>
  <table class="table">
    <thead><tr><th>ID</th><th>Donatur</th><th>Kampanye</th><th>Pohon</th><th>Total</th><th>Status</th></tr></thead>
    <tbody>
      @foreach($donasi as $d)
        <tr>
          <td data-l="ID" class="mono">#{{ $d->kode }}</td>
          <td data-l="Donatur">{{ $d->nama_donatur }}</td>
          <td data-l="Kampanye"><div class="cell-stack">{{ $d->kampanye->judul }}<small>{{ $d->kampanye->lokasi }}</small></div></td>
          <td data-l="Pohon" class="num">{{ number_format($d->jumlah_pohon, 0, ',', '.') }}</td>
          <td data-l="Total" class="num">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
          <td data-l="Status"><span class="badge {{ $d->status_badge }}"><span class="dot"></span>{{ $d->status_label }}</span></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="card mt-24">
  <div class="card-head">
    <h2 class="h5">Progres Kampanye Aktif</h2>
    <span class="small" style="font-size:12px">Diperbarui {{ now()->subMinutes(5)->diffForHumans() }}</span>
  </div>
  @foreach($progres as $k)
    @php $persen = $k->target_pohon ? min(100, round(($k->terkumpul ?? 0) / $k->target_pohon * 100)) : 0; @endphp
    <div class="prog-row">
      <div class="row between" style="font-size:13.5px;font-weight:700">
        <span>{{ $k->judul }}</span>
        <span class="num muted" style="font-weight:600">{{ number_format($k->terkumpul ?? 0, 0, ',', '.') }} / {{ number_format($k->target_pohon, 0, ',', '.') }} pohon</span>
      </div>
      <div class="progress {{ $persen >= 80 ? 'amber' : '' }}" style="margin:10px 0 8px"><i style="width:{{ $persen }}%"></i></div>
      <span class="num" style="font-size:12.5px;font-weight:800;color:var(--emerald)">{{ $persen }}% terkumpul</span>
    </div>
  @endforeach
</div>
@endsection
