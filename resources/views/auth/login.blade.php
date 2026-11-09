@extends('layouts.public')
@section('title', 'Masuk')

@section('body')
<div class="auth-wrap">
  <div class="auth-brand">
    <div class="container-inner">
      <div class="brand" style="margin-bottom:40px">
        <span class="brand-mark" style="background:rgba(255,255,255,.16)"><span data-icon="leaf" data-ic-class="ic" style="color:#fff"></span></span>
        <span>
          <span class="brand-name" style="color:#fff">Green-Fund</span>
          <span class="brand-sub" style="color:rgba(255,255,255,.55)">Crowdfunding Penghijauan</span>
        </span>
      </div>
      <h2 class="h1" style="font-size:44px;line-height:52px">Satu donasi, satu pohon tercatat.</h2>
      <p class="mt-16" style="color:rgba(255,255,255,.8);font-size:15px;line-height:26px">
        Platform crowdfunding penghijauan dengan pelaporan pertumbuhan pohon yang bisa kamu pantau transparan.
      </p>
      <div style="margin-top:32px">
        @foreach([
          ['shield', 'Donasi terverifikasi manual'],
          ['sprout', 'Log pertumbuhan tiap bulan'],
          ['trending', 'Laporan publik &amp; auditable'],
        ] as [$icon, $text])
          <div class="auth-feat">
            <span class="chip"><span data-icon="{{ $icon }}" data-ic-class="ic"></span></span>{{ $text }}
          </div>
        @endforeach
      </div>
      <div class="auth-stats">
        <div><b class="num">{{ number_format($totalPohon ?? 15482, 0, ',', '.') }}</b><span>Pohon ditanam</span></div>
        <div><b class="num">{{ $totalKampanye ?? 48 }}</b><span>Proyek aktif</span></div>
        <div><b class="num">Rp 450M</b><span>Dana tersalur</span></div>
      </div>
    </div>
  </div>

  <div class="auth-form">
    <div class="inner">
      <span class="eyebrow">Selamat datang kembali</span>
      <h1 class="h2 mt-8" style="font-size:32px;line-height:40px">Masuk ke akunmu</h1>
      <p class="small mt-8">Lanjutkan donasi dan pantau pohon yang sudah kamu tanam.</p>

      <a href="{{ route('katalog') }}" class="btn btn-outline btn-block mt-24">
        <span data-icon="globe" data-ic-class="ic-sm"></span>Masuk dengan Google</a>

      <div class="divider-label">atau</div>

      <form method="post" action="{{ route('login.attempt') }}">
        @csrf
        <div class="field">
          <label class="label" for="email">Email</label>
          <div class="input">
            <span data-icon="mail" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
          </div>
          @error('email')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="field mt-16">
          <label class="label" for="password">Kata Sandi</label>
          <div class="input">
            <span data-icon="lock" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input id="password" type="password" name="password" placeholder="Masukkan kata sandi" required>
            <button type="button" onclick="const p=document.getElementById('password');p.type=p.type==='password'?'text':'password'" aria-label="Tampilkan kata sandi" style="color:var(--muted);display:flex">
              <span data-icon="eye" data-ic-class="ic-sm"></span></button>
          </div>
          @error('password')<p class="err">{{ $message }}</p>@enderror
        </div>

        <div class="row between mt-16">
          <label class="checkbox"><input type="checkbox" name="ingat" value="1">Ingat saya</label>
          <a href="#" class="small" style="color:var(--emerald);font-weight:700">Lupa kata sandi?</a>
        </div>

        <button class="btn btn-primary btn-block mt-24">Masuk
          <span data-icon="arrow-right" data-ic-class="ic-sm"></span></button>
      </form>

      <p class="small mt-16" style="text-align:center">Belum punya akun?
        <a href="{{ route('register') }}" style="color:var(--emerald);font-weight:700">Daftar sekarang</a></p>

      <div class="note-pill mt-24">
        <span data-icon="shield" data-ic-class="ic-sm"></span>
        Data terenkripsi &amp; tidak dibagikan ke pihak ketiga
      </div>
    </div>
  </div>
</div>
@endsection
