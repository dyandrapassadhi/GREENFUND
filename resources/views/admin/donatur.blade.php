@extends('layouts.admin')
@section('title', 'Donatur')

@section('body')
<div class="admin-head">
  <div>
    <h1 class="h2" style="font-size:30px;line-height:38px">Donatur</h1>
    <p class="small mt-8">Daftar donatur teragregasi per email beserta total dukungannya.</p>
  </div>
</div>

<div class="card" style="overflow:hidden">
  <div class="card-head"><h2 class="h5">{{ $rows->total() }} donatur</h2>
    <span class="small" style="font-size:12px">Diperbarui {{ now()->translatedFormat('d M Y') }}</span></div>
  <table class="table">
    <thead><tr><th>Donatur</th><th>Transaksi</th><th>Pohon</th><th>Nilai Donasi</th><th>Terakhir</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td data-l="Donatur">
            <div class="row gap-12">
              <span class="avatar sm">{{ collect(explode(' ', $r->nama_donatur))->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('') }}</span>
              <div class="cell-stack">{{ $r->nama_donatur }}<small>{{ $r->email }}</small></div>
            </div>
          </td>
          <td data-l="Transaksi" class="num">{{ $r->transaksi }}</td>
          <td data-l="Pohon" class="num">{{ number_format($r->pohon, 0, ',', '.') }}</td>
          <td data-l="Nilai Donasi" class="num">Rp {{ number_format($r->nilai, 0, ',', '.') }}</td>
          <td data-l="Terakhir" class="small">{{ \Illuminate\Support\Carbon::parse($r->terakhir)->translatedFormat('d M Y') }}</td>
        </tr>
      @empty
        <tr><td colspan="5">
          <div class="empty">
            <span class="chip"><span data-icon="users" data-ic-class="ic-lg"></span></span>
            <h3 class="h5">Belum ada donatur</h3>
            <p class="small">Donatur muncul setelah ada donasi masuk.</p>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($rows->hasPages())<div class="mt-16">{{ $rows->links() }}</div>@endif
@endsection
