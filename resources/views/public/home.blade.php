@extends('layouts.public')
@section('title', 'Tanam Pohon Hari Ini')

@section('body')
<section class="container">
  <div class="hero">
    <div>
      <span class="badge badge-green" style="height:30px;padding:0 14px">
        <span data-icon="star" data-ic-class="ic-sm"></span>SDG 13 &middot; SDG 15 &middot; Climate &amp; Life on Land
      </span>
      <h1 class="h1 mt-24">Tanam Pohon Hari Ini,<br>Warisi Hutan untuk Besok</h1>
      <p class="body mt-16" style="max-width:520px">
        Platform crowdfunding &amp; monitoring penanaman pohon yang transparan. Setiap rupiah tercatat,
        setiap bibit terpantau lewat log perkembangan berfoto.
      </p>
      <div class="row gap-12 mt-32" style="flex-wrap:wrap">
        <a href="{{ route('katalog') }}" class="btn btn-primary btn-lg">Jelajahi Kampanye
          <span data-icon="arrow-right" data-ic-class="ic-sm"></span></a>
        <a href="#cara-kerja" class="btn btn-outline btn-lg">Cara Kerja</a>
      </div>
      <div class="hero-checks">
        <span><span data-icon="shield" data-ic-class="ic-sm"></span>Donasi Aman</span>
        <span><span data-icon="check" data-ic-class="ic-sm"></span>Terverifikasi</span>
        <span><span data-icon="file" data-ic-class="ic-sm"></span>Laporan Publik</span>
      </div>
    </div>
    <div>
      <div class="hero-panel" @if($heroImage ?? null) data-photo="1" style="--hero-img:url('{{ $heroImage }}')" @endif>
        @unless($heroImage ?? null)
          <span class="ring r1"></span><span class="ring r2"></span>
          <span data-icon="tree" data-ic-class="tree"></span>
        @endunless
      </div>
      <div class="stat-strip">
        <div class="stat-mini"><b class="num">{{ number_format($stats['totalPohon'], 0, ',', '.') }}</b><span>Pohon Ditanam</span></div>
        <div class="stat-mini"><b class="num">Rp {{ number_format($stats['totalPohon'] * 25000 / 1000000, 0, ',', '.') }}M</b><span>Dana Terkumpul</span></div>
        <div class="stat-mini"><b class="num">{{ $stats['totalKampanye'] }}</b><span>Proyek Selesai</span></div>
      </div>
    </div>
  </div>
</section>

<section class="section alt">
  <div class="container">
    <div class="row between mb-32" style="flex-wrap:wrap;gap:16px">
      <div>
        <span class="eyebrow">Kampanye Unggulan</span>
        <h2 class="h2 mt-8">Pilih pohon yang ingin kamu tanam</h2>
      </div>
      <a href="{{ route('katalog') }}" class="btn btn-outline">Lihat semua
        <span data-icon="arrow-right" data-ic-class="ic-sm"></span></a>
    </div>
    <div class="grid-3">
      @foreach($unggulan as $k)
        @include('partials.campaign-card', ['k' => $k])
      @endforeach
    </div>
  </div>
</section>

<section class="section" id="cara-kerja">
  <div class="container">
    <div class="grid-2" style="align-items:start">
      <div style="background:linear-gradient(150deg,#1B4D33,#2D6A4F);border-radius:20px;padding:40px;color:#fff">
        <span class="eyebrow" style="color:var(--leaf)">Transparansi</span>
        <h3 class="h2 mt-8" style="font-size:32px;line-height:40px">Dari donasi ke daun</h3>
        <div style="margin-top:28px;display:flex;flex-direction:column;gap:20px">
          @foreach(['Donasi masuk', 'Bukti transfer diverifikasi', 'Bibit ditanam & difoto', 'Log perkembangan tiap bulan'] as $i => $step)
            <div class="row gap-16">
              <span style="width:32px;height:32px;border-radius:50%;background:var(--amber);color:#1D2D44;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;flex:0 0 auto">{{ $i + 1 }}</span>
              <span style="font-size:15px;font-weight:600">{{ $step }}</span>
            </div>
          @endforeach
        </div>
        <div class="row gap-12" style="background:rgba(255,255,255,.1);border-radius:12px;padding:14px 16px;margin-top:32px">
          <span data-icon="shield" data-ic-class="ic"></span>
          <span>
            <span style="display:block;font-size:14px;font-weight:700">100% donasi tercatat</span>
            <span style="display:block;font-size:12px;color:rgba(255,255,255,.7)">Audit publik tiap kuartal</span>
          </span>
        </div>
      </div>
      <div>
        <span class="eyebrow">Dampak &amp; Transparansi</span>
        <h2 class="h2 mt-8">Dua tujuan global, satu akar masalah</h2>
        <p class="body mt-16">
          Green-Fund menghubungkan donatur langsung ke petani bibit lokal. Setiap kampanye menyumbang
          pada SDG 13 (Climate Action) dan SDG 15 (Life on Land), dengan bukti tanam dan log foto
          yang bisa kamu periksa kapan saja.
        </p>
        <div class="col gap-12 mt-24">
          @foreach([
            ['13', 'Climate Action', 'Setiap pohon menyerap ±22 kg CO₂ per tahun.'],
            ['15', 'Life on Land', 'Restorasi habitat endemik & tutupan lahan.'],
            ['17', 'Partnerships', 'Kolaborasi petani bibit & komunitas lokal.'],
          ] as [$no, $title, $desc])
            <div class="card card-pad row gap-16" style="align-items:flex-start">
              <span style="width:44px;height:44px;border-radius:12px;background:var(--leaf-soft);color:var(--forest);font-weight:800;display:flex;align-items:center;justify-content:center;flex:0 0 auto">{{ $no }}</span>
              <span>
                <span class="h5" style="display:block;text-transform:uppercase;font-size:13px;letter-spacing:.8px">{{ $title }}</span>
                <span class="small" style="display:block;margin-top:4px">{{ $desc }}</span>
              </span>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container" id="dampak" style="padding-bottom:72px">
  <div class="cta-banner">
    <div>
      <h2 class="h2 white" style="font-size:32px;line-height:40px">Siap menanam pohon pertamamu?</h2>
      <p class="mt-8" style="color:rgba(255,255,255,.85);font-size:15px">Mulai dari Rp 25.000, satu bibit satu masa depan.</p>
    </div>
    <a href="{{ route('katalog') }}" class="btn btn-amber btn-lg">Mulai Donasi
      <span data-icon="arrow-right" data-ic-class="ic-sm"></span></a>
  </div>
</section>
@endsection
