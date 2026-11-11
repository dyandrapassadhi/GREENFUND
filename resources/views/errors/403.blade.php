@extends('layouts.public')
@section('title', 'Akses Ditolak')

@section('body')
<div class="container" style="padding:80px 24px 120px">
  <div class="card card-pad" style="max-width:520px;margin:0 auto;text-align:center">
    <span style="width:64px;height:64px;border-radius:18px;background:var(--danger-soft);color:var(--danger);display:flex;align-items:center;justify-content:center;margin:0 auto 20px">
      <span data-icon="shield" data-ic-class="ic" style="width:30px;height:30px"></span>
    </span>
    <h1 class="h3">Halaman ini khusus admin</h1>
    <p class="small mt-8">
      Akun kamu tidak punya akses ke panel admin. Kalau kamu pengelola yayasan,
      masuk memakai akun admin yang diberikan.
    </p>
    <div class="row gap-12 mt-24 center" style="flex-wrap:wrap;justify-content:center">
      <a href="{{ route('katalog') }}" class="btn btn-primary btn-sm">Ke katalog kampanye</a>
      <a href="{{ route('home') }}" class="btn btn-outline btn-sm">Halaman utama</a>
    </div>
  </div>
</div>
@endsection
