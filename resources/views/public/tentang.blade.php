@extends('layouts.public')
@section('title', 'Tentang SDG')

@section('body')
<div class="container">
  <nav class="crumbs">
    <a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Tentang SDG</span>
  </nav>

  <div class="hero-katalog-head">
    <span class="eyebrow">Tentang Kami</span>
    <h1 class="h1 mt-8" style="font-size:44px;line-height:52px">Penghijauan yang bisa diaudit siapa saja</h1>
    <p class="body mt-8" style="max-width:640px">
      Green-Fund adalah platform crowdfunding penghijauan yang menyambungkan donatur langsung dengan
      petani bibit lokal. Fokus kami sederhana: bukti tanam yang jujur dan progres yang bisa dipantau.
    </p>
  </div>

  <div class="stat-strip" style="margin-bottom:48px">
    <div class="stat-mini"><b class="num">{{ number_format($stats['totalPohon'], 0, ',', '.') }}</b><span>Pohon ditanam</span></div>
    <div class="stat-mini"><b class="num">{{ $stats['aktif'] }}</b><span>Kampanye aktif</span></div>
    <div class="stat-mini"><b class="num">{{ $stats['totalKampanye'] }}</b><span>Total kampanye</span></div>
  </div>

  <div class="grid-2" style="padding-bottom:72px">
    @foreach([
      ['star', 'SDG 13 — Climate Action', 'Setiap pohon yang ditanam menyerap ±22 kg CO₂ per tahun. Log pertumbuhan bulanan memastikan bibit benar-benar hidup, bukan hanya ditanam.'],
      ['sprout', 'SDG 15 — Life on Land', 'Restorasi mangrove, gambut, dan daerah aliran sungai mengembalikan habitat endemik serta menahan abrasi pesisir.'],
      ['users', 'SDG 17 — Partnerships', 'Kami membeli bibit dari petani lokal, sehingga donasi berputar di ekonomi desa sekaligus menjaga tutupan lahan.'],
      ['shield', 'Transparansi Dana', 'Setiap donasi punya kode invoice, bukti transfer diverifikasi manual, dan rekap dana diterbitkan tiap kuartal.'],
    ] as [$icon, $title, $desc])
      <div class="card card-pad row gap-16" style="align-items:flex-start">
        <span style="width:44px;height:44px;border-radius:12px;background:var(--leaf-soft);color:var(--forest);display:flex;align-items:center;justify-content:center;flex:0 0 auto">
          <span data-icon="{{ $icon }}" data-ic-class="ic"></span></span>
        <span>
          <h3 class="h5">{{ $title }}</h3>
          <p class="small mt-8">{{ $desc }}</p>
        </span>
      </div>
    @endforeach
  </div>
</div>
@endsection
