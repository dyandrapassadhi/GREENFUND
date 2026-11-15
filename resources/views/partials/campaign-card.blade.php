@php
  $persen = $k->target_pohon > 0 ? (int) round(min(100, ($k->terkumpul ?? 0) / $k->target_pohon * 100)) : 0;
  $coverUrl = $k->cover ? \Illuminate\Support\Facades\Storage::disk('public')->url($k->cover) : null;
@endphp
<article class="camp-card">
  <a href="{{ route('katalog.show', $k) }}" class="camp-cover" @if($coverUrl) style="background-image:url('{{ $coverUrl }}');background-size:cover;background-position:center" @endif>
    @unless($coverUrl)
      <span class="ring a"></span><span class="ring b"></span>
      <span data-icon="tree" data-ic-class="tree"></span>
    @endunless
    <span class="badge badge-amber">{{ $k->kategori }}</span>
  </a>
  <div class="camp-body">
    <span class="camp-loc"><span data-icon="map-pin" data-ic-class="ic-sm"></span>{{ $k->lokasi }}</span>
    <a href="{{ route('katalog.show', $k) }}" class="h5" style="line-height:24px">{{ $k->judul }}</a>
    <div class="progress" style="margin-top:auto"><i style="width:{{ $persen }}%"></i></div>
    <div class="progress-meta">
      <b class="num">{{ number_format($k->terkumpul ?? 0, 0, ',', '.') }} pohon</b>
      <span class="num">dari {{ number_format($k->target_pohon, 0, ',', '.') }}</span>
    </div>
    <a href="{{ route('katalog.show', $k) }}" class="btn btn-primary btn-block" style="height:42px;font-size:14px;margin-top:6px">
      {{ auth()->check() ? 'Donasi' : 'Masuk untuk Donasi' }} <span data-icon="arrow-right" data-ic-class="ic-sm"></span>
    </a>
  </div>
</article>
