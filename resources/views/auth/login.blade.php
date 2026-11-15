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

      @php $googleReady = filled(config('services.google.client_id')); @endphp
      <a href="{{ route('google.redirect') }}" class="btn btn-outline btn-block mt-24"
         @unless($googleReady) aria-disabled="true" @endunless>
        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5a5.6 5.6 0 0 1-2.4 3.6v3h3.9c2.3-2.1 3.5-5.2 3.5-8.8Z"/>
          <path fill="#34A853" d="M12 24c3.2 0 5.9-1.1 7.9-2.9l-3.9-3c-1.1.7-2.4 1.2-4 1.2-3.1 0-5.7-2.1-6.6-4.9H1.4v3.1A12 12 0 0 0 12 24Z"/>
          <path fill="#FBBC05" d="M5.4 14.4a7.2 7.2 0 0 1 0-4.6V6.7H1.4a12 12 0 0 0 0 10.8l4-3.1Z"/>
          <path fill="#EA4335" d="M12 4.8c1.8 0 3.3.6 4.6 1.8l3.4-3.4A12 12 0 0 0 1.4 6.7l4 3.1C6.3 6.9 8.9 4.8 12 4.8Z"/>
        </svg>
        Masuk dengan Google</a>
      @unless($googleReady)
        <p class="tiny mt-8" style="text-align:center;color:var(--muted)">
          Google login belum aktif. Isi <code>GOOGLE_CLIENT_ID</code> di <code>.env</code>
        </p>
      @endunless

      <div class="divider-label">atau</div>

      <form method="post" action="{{ route('login.attempt') }}">
        @csrf
        <div class="field">
          <label class="label" for="login">Email atau Username</label>
          <div class="input">
            <span data-icon="mail" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input id="login" type="text" name="login" value="{{ old('login') }}"
                   placeholder="nama@email.com atau username admin" required autofocus>
          </div>
          @error('login')<p class="err">{{ $message }}</p>@enderror
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
