@extends('layouts.admin')
@section('title', 'Verifikasi Donasi')

@section('body')
<div class="admin-head">
  <div>
    <h1 class="h2" style="font-size:30px;line-height:38px">Verifikasi Donasi</h1>
    <p class="small mt-8">Periksa bukti transfer lalu tandai donasi sebagai terverifikasi atau ditolak.</p>
  </div>
  <form class="search-box" method="get" action="{{ route('admin.donasi.index') }}">
    <span data-icon="search" data-ic-class="ic-sm"></span>
    <input name="q" placeholder="Cari kode / donatur" value="{{ request('q') }}">
  </form>
</div>

<div class="filters" style="margin-bottom:16px">
  <div class="pills">
    @foreach(['semua' => 'Semua', 'pending' => 'Menunggu', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $val => $txt)
      <a href="{{ route('admin.donasi.index', $val === 'semua' ? [] : ['status' => $val]) }}"
         class="pill {{ $status === $val ? 'active' : '' }}">{{ $txt }}</a>
    @endforeach
  </div>
  <span class="small"><b style="color:var(--text)">{{ $donasi->total() }}</b> donasi</span>
</div>

<div class="card" style="overflow:hidden">
  <table class="table">
    <thead><tr><th>ID</th><th>Donatur</th><th>Kampanye</th><th>Bukti</th><th>Total</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
    <tbody>
      @forelse($donasi as $d)
        <tr>
          <td data-l="ID" class="mono">#{{ $d->kode }}</td>
          <td data-l="Donatur"><div class="cell-stack">{{ $d->nama_donatur }}<small>{{ $d->email }}</small></div></td>
          <td data-l="Kampanye"><div class="cell-stack">{{ $d->kampanye->judul }}<small>{{ $d->jumlah_pohon }} pohon</small></div></td>
          <td data-l="Bukti">
            @if($d->bukti)
              <a href="{{ asset('storage/'.$d->bukti) }}" target="_blank" class="badge badge-green"><span data-icon="check" data-ic-class="ic-sm"></span>Ada</a>
            @else
              <span class="badge badge-slate">Belum</span>
            @endif
          </td>
          <td data-l="Total" class="num">Rp {{ number_format($d->total, 0, ',', '.') }}</td>
          <td data-l="Status"><span class="badge {{ $d->status_badge }}"><span class="dot"></span>{{ $d->status_label }}</span></td>
          <td data-l="Aksi" class="text-right" style="white-space:nowrap">
            <form method="post" action="{{ route('admin.donasi.status', $d) }}" style="display:inline">
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="terverifikasi">
              <button class="btn btn-outline btn-sm" title="Tandai terverifikasi" style="height:30px;padding:0 10px;font-size:12px">Setujui</button>
            </form>
            <form method="post" action="{{ route('admin.donasi.status', $d) }}" style="display:inline">
              @csrf @method('PATCH')
              <input type="hidden" name="status" value="ditolak">
              <button class="btn btn-danger btn-sm" title="Tolak" style="height:30px;padding:0 10px;font-size:12px">Tolak</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="7">
          <div class="empty">
            <span class="chip"><span data-icon="wallet" data-ic-class="ic-lg"></span></span>
            <h3 class="h5">Tidak ada donasi</h3>
            <p class="small">Belum ada donasi yang cocok dengan filter ini.</p>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($donasi->hasPages())<div class="mt-16">{{ $donasi->links() }}</div>@endif
@endsection
