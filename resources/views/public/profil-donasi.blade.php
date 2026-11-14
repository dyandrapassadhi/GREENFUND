@extends('layouts.public')
@section('title', 'Detail Donasi '.$donasi->kode)

@php
  $k = $donasi->kampanye;
  $terkumpul = (int) $k->donasi()->where('status','terverifikasi')->sum('jumlah_pohon');
  $target = max(1, $k->target_pohon);
  $persen = (int) round(min(100, $terkumpul / $target * 100));
@endphp

@section('body')
<div class="container">
  <nav class="crumbs">
    <a href="{{ route('home') }}">Home</a><span class="sep">/</span>
    <a href="{{ route('profil') }}">Profil</a><span class="sep">/</span>
    <span class="current">{{ $donasi->kode }}</span>
  </nav>

  <div class="detail-grid mt-8">
    <div>
      <span class="eyebrow">Tracking Donasi</span>
      <h1 class="h2 mt-8" style="font-size:30px;line-height:38px">{{ $k->judul }}</h1>
      <div class="row gap-8 mt-16" style="flex-wrap:wrap">
        <span class="badge {{ $donasi->status_badge }}"><span class="dot"></span>{{ $donasi->status_label }}</span>
        <span class="badge badge-slate mono">{{ $donasi->kode }}</span>
        <span class="badge badge-slate">{{ $donasi->created_at->translatedFormat('d M Y') }}</span>
      </div>

      <div class="card card-pad mt-24">
        <h2 class="h4 mb-16">Donasi kamu</h2>
        <div class="inv-line"><span>Jumlah pohon</span><b class="num">{{ number_format($donasi->jumlah_pohon, 0, ',', '.') }} pohon</b></div>
        <div class="inv-line"><span>Subtotal</span><b class="num">Rp {{ number_format($donasi->subtotal, 0, ',', '.') }}</b></div>
        <div class="inv-line"><span>Biaya administrasi</span><b class="num">Rp {{ number_format($donasi->biaya_admin, 0, ',', '.') }}</b></div>
        <div class="inv-line"><span>Kode unik</span><b class="num">Rp {{ $donasi->kode_unik }}</b></div>
        <div class="inv-line total"><span>Total</span><span class="num" style="color:var(--forest)">Rp {{ number_format($donasi->total, 0, ',', '.') }}</span></div>
        @if($donasi->paid_at)
          <p class="tiny mt-16">Lunas diverifikasi {{ \Illuminate\Support\Carbon::parse($donasi->paid_at)->translatedFormat('d M Y, H:i') }} WIB</p>
        @endif
      </div>

      <div class="card" style="padding:24px;margin-top:16px">
        <div class="row between mb-24" style="flex-wrap:wrap;gap:8px">
          <div>
            <h2 class="h4">Progres penanaman</h2>
            <p class="small">Log lapangan dari kampanye yang kamu dukung</p>
          </div>
          <span class="badge badge-slate">{{ $k->logs->count() }} entri</span>
        </div>

        @if($k->logs->isEmpty())
          <div class="empty">
            <span class="chip"><span data-icon="sprout" data-ic-class="ic-lg"></span></span>
            <h3 class="h5">Belum ada log penanaman</h3>
            <p class="small">Tim lapangan akan mengunggah log setelah penanaman pertama.</p>
          </div>
        @else
          <div class="timeline">
            @foreach($k->logs as $log)
              <div class="tl-item">
                <div class="tl-head">
                  <span>{{ $log->tanggal->translatedFormat('d M Y') }}</span>
                  <span class="badge {{ $log->kondisi === 'baik' ? 'badge-green' : 'badge-amber' }}">
                    <span class="dot"></span>{{ $log->kondisi_label }}</span>
                </div>
                <h3 class="h5">{{ $log->judul }}</h3>
                <p class="small" style="margin-top:4px">{{ $log->catatan }}</p>
                @if($log->foto)
                  <div class="tl-photos">
                    <div class="tl-photo" style="background-image:url('{{ asset('storage/'.$log->foto) }}');background-size:cover;background-position:center"></div>
                  </div>
                @endif
              </div>
            @endforeach
          </div>
        @endif
      </div>
    </div>

    <aside class="sticky">
      <div class="sum-card">
        <h2 class="h5 mb-16">Progres kampanye</h2>
        <div class="progress"><i style="width:{{ $persen }}%"></i></div>
        <div class="row between mt-8">
          <b class="num" style="font-size:13.5px">{{ number_format($terkumpul, 0, ',', '.') }} / {{ number_format($target, 0, ',', '.') }}</b>
          <b class="num" style="font-size:13.5px;color:var(--emerald)">{{ $persen }}%</b>
        </div>
        <div class="sum-price mt-16">
          <span>Kontribusi kamu</span>
          <span class="num">{{ number_format($donasi->jumlah_pohon, 0, ',', '.') }} pohon</span>
        </div>
        <div class="sum-price mt-8">
          <span>Porsi kamu</span>
          <span class="num">{{ $terkumpul > 0 ? round($donasi->jumlah_pohon / $terkumpul * 100, 2) : 0 }}%</span>
        </div>

        <a href="{{ route('donasi.invoice', $donasi->kode) }}" class="btn btn-outline btn-block mt-16">
          <span data-icon="file" data-ic-class="ic-sm"></span>Lihat invoice</a>
        @if($donasi->status === 'pending')
          <a href="{{ route('donasi.invoice', $donasi->kode) }}" class="btn btn-primary btn-block mt-8">
            Bayar sekarang <span data-icon="arrow-right" data-ic-class="ic-sm"></span></a>
        @endif
        <a href="{{ route('profil') }}" class="btn btn-ghost btn-block mt-8">Kembali ke profil</a>
      </div>

      <div class="sum-card mt-16">
        <h3 class="h5 mb-16">Kampanye</h3>
        <div class="row gap-12">
          <span class="avatar" style="background:var(--forest);color:#fff">
            <span data-icon="tree" data-ic-class="ic-sm"></span></span>
          <span>
            <b style="font-size:13.5px;display:block">{{ $k->penyelenggara }}</b>
            <small class="small" style="font-size:12px">{{ $k->lokasi }}</small>
          </span>
        </div>
        <a href="{{ route('katalog.show', $k) }}" class="btn btn-outline btn-sm btn-block mt-16">Buka kampanye</a>
      </div>
    </aside>
  </div>
</div>
@endsection
