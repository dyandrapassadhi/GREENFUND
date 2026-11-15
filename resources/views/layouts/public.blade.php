<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Green-Fund') | Green-Fund</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/greenfund.css') }}">
</head>
<body>
@php
  $initials = collect(explode(' ', auth()->user()?->name ?? 'GF'))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
@endphp

<header class="site-header">
  <div class="container">
    <a href="{{ route('home') }}" class="brand">
      <span class="brand-mark"><span data-icon="leaf" data-ic-class="ic" style="color:#fff"></span></span>
      <span>
        <span class="brand-name">Green-Fund</span>
        <span class="brand-sub">Crowdfunding Penghijauan</span>
      </span>
    </a>
    <nav class="nav">
      <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
      <a href="{{ route('katalog') }}" class="{{ request()->routeIs('katalog') ? 'active' : '' }}">Katalog Kampanye</a>
      <a href="{{ route('tentang-sdg') }}" class="{{ request()->routeIs('tentang-sdg') ? 'active' : '' }}">Tentang SDG</a>
    </nav>
    <div class="row gap-12">
      @auth
        @if(auth()->user()->isAdmin())
          <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">Panel Admin</a>
        @endif
        <a href="{{ route('profil') }}" class="user-chip {{ request()->routeIs('profil*') ? 'active' : '' }}" title="Profil saya">
          @if(auth()->user()->avatar)
            <img src="{{ auth()->user()->avatar }}" alt="" class="user-chip-avatar" referrerpolicy="no-referrer">
          @else
            <span class="user-chip-avatar">{{ collect(explode(' ', auth()->user()->name))->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('') }}</span>
          @endif
          <span class="user-chip-name">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 16) }}</span>
        </a>
      @else
        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
        <a href="{{ auth()->check() ? route('katalog') : route('login') }}" class="btn btn-primary btn-sm">Donasi Sekarang
          <span data-icon="arrow-right" data-ic-class="ic-sm"></span></a>
      @endauth
    </div>
  </div>
</header>

@yield('body')

<footer class="footer">
  <div class="container">
    <div class="cols">
      <div>
        <div class="brand" style="margin-bottom:16px">
          <span class="brand-mark"><span data-icon="leaf" data-ic-class="ic" style="color:#fff"></span></span>
          <span>
            <span class="brand-name" style="color:#fff">Green-Fund</span>
            <span class="brand-sub" style="color:rgba(255,255,255,.5)">Crowdfunding Penghijauan</span>
          </span>
        </div>
        <p style="font-size:14px;line-height:24px;color:rgba(255,255,255,.7);max-width:320px;margin-bottom:16px">
          Platform crowdfunding penghijauan yang menghubungkan donatur dengan petani bibit lokal, dengan pelaporan pertumbuhan pohon yang transparan.
        </p>
        <div class="row gap-8">
          <span class="badge badge-green">SDG 13</span>
          <span class="badge badge-green">SDG 15</span>
        </div>
      </div>
      <div>
        <h6>Platform</h6>
        <a href="{{ route('katalog') }}">Katalog Kampanye</a>
        <a href="{{ route('home') }}#cara-kerja">Cara Kerja</a>
        <a href="{{ route('home') }}#dampak">Laporan Dampak</a>
        <a href="{{ route('tentang-sdg') }}">FAQ</a>
      </div>
      <div>
        <h6>Organisasi</h6>
        <a href="{{ route('tentang-sdg') }}">Tentang Kami</a>
        <a href="{{ route('tentang-sdg') }}">Mitra Petani</a>
        <a href="{{ route('tentang-sdg') }}">Karier</a>
        <a href="mailto:hubungi@greenfund.id">Kontak</a>
      </div>
      <div>
        <h6>Legal</h6>
        <a href="{{ route('tentang-sdg') }}">Kebijakan Privasi</a>
        <a href="{{ route('tentang-sdg') }}">Syarat Layanan</a>
        <a href="{{ route('tentang-sdg') }}">Transparansi Dana</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; {{ date('Y') }} Green-Fund. Dibuat untuk bumi yang lebih hijau.</span>
      <div class="row gap-8" style="align-items:center">
        <a href="#" class="social" aria-label="Instagram Green-Fund" title="Instagram">
          <span data-icon="instagram" data-ic-class="ic-sm"></span></a>
        <a href="#" class="social" aria-label="X (Twitter) Green-Fund" title="X (Twitter)">
          <span data-icon="twitter" data-ic-class="ic-sm"></span></a>
        <a href="mailto:hubungi@greenfund.id" class="social" aria-label="Email Green-Fund" title="Email kami">
          <span data-icon="globe" data-ic-class="ic-sm"></span></a>
      </div>
    </div>
  </div>
</footer>

@include('partials.icon-lib')
@stack('scripts')
</body>
</html>
