@extends('layouts.public')
@section('title', 'Katalog Kampanye')

@section('body')
<div class="container">
  <nav class="crumbs">
    <a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Katalog Kampanye</span>
  </nav>

  <div class="hero-katalog-head">
    <span class="eyebrow">Katalog Kampanye</span>
    <h1 class="h1 mt-8" style="font-size:44px;line-height:52px">Semua kampanye penghijauan</h1>
    <p class="body mt-8">Pilih kampanye yang ingin kamu dukung. Setiap donasi tercatat dan dilaporkan pertumbuhannya.</p>
  </div>

  <div class="filters">
    <div class="pills">
      @foreach(['Semua', 'Penghijauan', 'Mangrove', 'Restorasi DAS', 'Gambut'] as $kat)
        <a href="{{ route('katalog', $kat === 'Semua' ? [] : ['kategori' => $kat]) }}"
           class="pill {{ $kategori === $kat ? 'active' : '' }}">{{ $kat }}</a>
      @endforeach
    </div>
    <span class="small"><b style="color:var(--text)">{{ $kampanye->total() }} kampanye</b> aktif</span>
  </div>

  <div class="grid-3" style="padding-bottom:56px">
    @forelse($kampanye as $k)
      @include('partials.campaign-card', ['k' => $k])
    @empty
      <div class="card empty" style="grid-column:1/-1">
        <span class="chip"><span data-icon="sprout" data-ic-class="ic-lg"></span></span>
        <h3 class="h4">Belum ada kampanye di kategori ini</h3>
        <p class="small">Coba lihat semua kampanye yang sedang berjalan.</p>
        <a href="{{ route('katalog') }}" class="btn btn-primary btn-sm">Lihat semua kampanye</a>
      </div>
    @endforelse
  </div>

  @if($kampanye->hasPages())
    <div style="padding-bottom:56px">{{ $kampanye->links() }}</div>
  @endif
</div>
@endsection
