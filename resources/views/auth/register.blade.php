@extends('layouts.public')
@section('title', 'Daftar Akun')

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
      <h2 class="h1" style="font-size:44px;line-height:52px">Mulai dari satu bibit.</h2>
      <p class="mt-16" style="color:rgba(255,255,255,.8);font-size:15px;line-height:26px">
        Buat akun untuk menyimpan riwayat donasi dan memantau pohon yang kamu tanam.
      </p>
      <div style="margin-top:32px">
        @foreach([['check', 'Riwayat donasi tersimpan rapi'], ['sprout', 'Notifikasi log perkembangan'], ['file', 'Invoice &amp; bukti donasi digital']] as [$icon, $text])
          <div class="auth-feat"><span class="chip"><span data-icon="{{ $icon }}" data-ic-class="ic"></span></span>{{ $text }}</div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="auth-form">
    <div class="inner">
      <span class="eyebrow">Akun baru</span>
      <h1 class="h2 mt-8" style="font-size:32px;line-height:40px">Daftar akun donatur</h1>
      <p class="small mt-8">Gratis, tidak ada biaya pendaftaran.</p>

      <form method="post" action="{{ route('register.store') }}" class="mt-24">
        @csrf
        <div class="field">
          <label class="label">Nama lengkap</label>
          <div class="input"><span data-icon="users" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required></div>
          @error('name')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div class="field mt-16">
          <label class="label">Email</label>
          <div class="input"><span data-icon="mail" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required></div>
          @error('email')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div class="field mt-16">
          <label class="label">Kata sandi</label>
          <div class="input"><span data-icon="lock" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required></div>
          @error('password')<p class="err">{{ $message }}</p>@enderror
        </div>
        <div class="field mt-16">
          <label class="label">Ulangi kata sandi</label>
          <div class="input"><span data-icon="lock" data-ic-class="ic-sm" style="color:var(--muted)"></span>
            <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required></div>
        </div>

        <button class="btn btn-primary btn-block mt-24">Buat akun
          <span data-icon="arrow-right" data-ic-class="ic-sm"></span></button>
      </form>

      <p class="small mt-16" style="text-align:center">Sudah punya akun?
        <a href="{{ route('login') }}" style="color:var(--emerald);font-weight:700">Masuk</a></p>
    </div>
  </div>
</div>
@endsection
