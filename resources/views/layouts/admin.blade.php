<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Admin') — Green-Fund Admin</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="{{ asset('css/greenfund.css') }}">
</head>
<body>
@php
  $u = auth()->user();
  $initials = collect(explode(' ', $u?->name ?? 'AD'))->take(2)->map(fn($w) => mb_substr($w, 0, 1))->implode('');
@endphp
<div class="admin-shell">
  <aside class="aside">
    <a href="{{ route('admin.dashboard') }}" class="brand">
      <span class="brand-mark" style="background:rgba(255,255,255,.16)"><span data-icon="leaf" data-ic-class="ic" style="color:#fff"></span></span>
      <span>
        <span class="brand-name" style="color:#fff">Green-Fund</span>
        <span class="brand-sub" style="color:rgba(255,255,255,.55)">Admin Panel</span>
      </span>
    </a>
    <nav>
      <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span data-icon="grid" data-ic-class="ic-sm"></span><span>Dashboard</span></a>
      <a href="{{ route('admin.kampanye.index') }}" class="{{ request()->routeIs('admin.kampanye.*') ? 'active' : '' }}">
        <span data-icon="layers" data-ic-class="ic-sm"></span><span>Kelola Kampanye</span></a>
      <a href="{{ route('admin.log.index') }}" class="{{ request()->routeIs('admin.log.*') ? 'active' : '' }}">
        <span data-icon="activity" data-ic-class="ic-sm"></span><span>Kelola Log Perkembangan</span></a>
      <a href="{{ route('admin.donasi.index') }}" class="{{ request()->routeIs('admin.donasi.*') ? 'active' : '' }}">
        <span data-icon="wallet" data-ic-class="ic-sm"></span><span>Verifikasi Donasi</span></a>
      <a href="{{ route('admin.donatur') }}" class="{{ request()->routeIs('admin.donatur') ? 'active' : '' }}">
        <span data-icon="users" data-ic-class="ic-sm"></span><span>Donatur</span></a>
    </nav>
    <div class="promo">
      Setiap donasi terverifikasi
      <b>{{ number_format($totalPohon ?? 0, 0, ',', '.') }} pohon &middot; {{ $totalKampanye ?? 0 }} proyek</b>
    </div>
    <div class="account">
      <span class="avatar amber">{{ $initials }}</span>
      <span class="grow">
        <span style="font-size:13px;font-weight:700;color:#fff">{{ $u?->name ?? 'Admin Yayasan' }}</span>
        <small>{{ $u?->username ? '@'.$u->username : $u?->email }}</small>
      </span>
      <form method="post" action="{{ route('logout') }}">@csrf
        <button title="Keluar" style="color:rgba(255,255,255,.7);display:flex"><span data-icon="log-out" data-ic-class="ic-sm"></span></button>
      </form>
    </div>
  </aside>

  <main class="admin-main">
    @yield('body')
  </main>
</div>

@if(session('ok'))
  <div class="flash"><span data-icon="check-circle" data-ic-class="ic-sm"></span>{{ session('ok') }}</div>
@endif
@include('partials.icon-lib')
@stack('scripts')
</body>
</html>
