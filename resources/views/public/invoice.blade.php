@extends('layouts.public')
@section('title', 'Invoice '.$donasi->kode)

@section('body')
<div class="container">
  <div class="invoice-wrap">
    <div class="card card-shadow" style="padding:32px">
      <div class="inv-head">
        <div>
          <h2 class="h3">Invoice <span class="num" style="color:var(--muted);font-weight:600">#{{ $donasi->kode }}</span></h2>
          <p class="small mt-8">Selesaikan pembayaran sebelum
            <b style="color:var(--text)">{{ $donasi->tenggat_bayar?->translatedFormat('d M Y, H:i') }} WIB</b></p>
        </div>
        <span class="badge {{ $donasi->status_badge }}">
          <span data-icon="clock" data-ic-class="ic-sm"></span>{{ $donasi->status_label }}</span>
      </div>

      <div class="divider" style="margin:24px 0"></div>

      <div class="inv-line">
        <span>{{ $donasi->kampanye->judul }} — {{ $donasi->jumlah_pohon }} pohon</span>
        <b class="num">Rp {{ number_format($donasi->subtotal, 0, ',', '.') }}</b>
      </div>
      <div class="inv-line">
        <span>Biaya administrasi</span>
        <b class="num">Rp {{ number_format($donasi->biaya_admin, 0, ',', '.') }}</b>
      </div>
      <div class="inv-line">
        <span>Kode unik transfer</span>
        <b class="num">Rp {{ $donasi->kode_unik }}</b>
      </div>
      <div class="inv-line total">
        <span>Total transfer</span>
        <span class="num" style="color:var(--forest)">Rp {{ number_format($donasi->total, 0, ',', '.') }}</span>
      </div>

      <h3 class="h5 mt-32 mb-16">Instruksi Pembayaran</h3>
      <div class="pay-grid">
        <div class="pay-box">
          <div class="row gap-8 mb-8"><span data-icon="wallet" data-ic-class="ic-sm"></span><b style="font-size:14px">Transfer Bank</b></div>
          <div class="pay-row"><span class="muted">Bank</span><b>Bank Mandiri</b></div>
          <div class="pay-row"><span class="muted">Nomor rekening</span><b class="num" id="norek">137-00-1234567-8</b></div>
          <div class="pay-row"><span class="muted">Atas nama</span><b>Yayasan Bakau Lestari</b></div>
          <button type="button" class="btn btn-outline btn-sm btn-block mt-8" data-copy="137-00-1234567-8">
            <span data-icon="copy" data-ic-class="ic-sm"></span>Salin nomor rekening</button>
        </div>
        <div class="pay-box">
          <div class="row gap-8 mb-8"><span data-icon="grid" data-ic-class="ic-sm"></span><b style="font-size:14px">QRIS</b></div>
          @if($dokuReady ?? false)
            <div style="display:flex;flex-direction:column;gap:10px;align-items:center;text-align:center;padding:10px 0">
              <span style="width:52px;height:52px;border-radius:15px;background:var(--leaf-soft);color:var(--emerald);display:flex;align-items:center;justify-content:center">
                <span data-icon="wallet" data-ic-class="ic" style="width:26px;height:26px"></span></span>
              <b style="font-size:14px">Bayar via DOKU</b>
              <span class="tiny">Pilih QRIS di halaman pembayaran DOKU. Nominal &amp; status terverifikasi otomatis.</span>
              <form method="post" action="{{ route('doku.pay', $donasi->kode) }}" style="width:100%">
                @csrf
                <button class="btn btn-primary btn-block" style="height:42px;font-size:14px">
                  Bayar dengan QRIS <span data-icon="arrow-right" data-ic-class="ic-sm"></span></button>
              </form>
              @error('doku')<p class="err">{{ $message }}</p>@enderror
            </div>
          @elseif($qrisPayload)
            <img src="{{ route('donasi.qris', $donasi->kode) }}" alt="QRIS pembayaran {{ $donasi->kode }}"
                 width="440" height="440" style="width:100%;height:auto;border-radius:10px;background:#fff;border:1px solid var(--border)">
            <p class="tiny" style="text-align:center;margin-top:8px">
              Nominal <b style="color:var(--text)">Rp {{ number_format($donasi->total, 0, ',', '.') }}</b> terisi otomatis
            </p>
          @else
            <div class="qris" style="flex-direction:column;gap:6px;height:auto;padding:18px">
              <span data-icon="grid" data-ic-class="ic" style="width:36px;height:36px"></span>
              <span class="tiny" style="text-align:center">QRIS belum aktif — gunakan transfer bank</span>
            </div>
          @endif
          @unless($dokuReady ?? false)
            <p class="tiny" style="text-align:center;margin-top:8px">Scan dengan m-banking / e-wallet</p>
          @endunless
        </div>
      </div>

      <form method="post" action="{{ route('donasi.bukti', $donasi->kode) }}" enctype="multipart/form-data" class="mt-24">
        @csrf
        <label class="dropzone {{ $donasi->bukti ? 'has-file' : '' }}" style="display:flex" id="dz">
          <input type="file" name="bukti" accept="image/png,image/jpeg" style="display:none" id="buktiInput">
          <span style="width:46px;height:46px;border-radius:14px;background:var(--leaf-soft);color:var(--emerald);display:flex;align-items:center;justify-content:center">
            <span data-icon="{{ $donasi->bukti ? 'check-circle' : 'upload' }}" data-ic-class="ic"></span>
          </span>
          <b style="font-size:15px" id="dzTitle">{{ $donasi->bukti ? 'Bukti sudah terunggah' : 'Unggah bukti transfer' }}</b>
          <span class="small" id="dzSub">{{ $donasi->bukti ? 'Terima kasih, sedang diverifikasi admin.' : 'PNG atau JPG, maks. 5 MB · pastikan nominal terlihat jelas' }}</span>
        </label>
        @error('bukti')<p class="err mt-8">{{ $message }}</p>@enderror

        <div class="row between mt-24" style="flex-wrap:wrap;gap:12px">
          <button class="btn btn-primary">Kirim Bukti Pembayaran
            <span data-icon="check" data-ic-class="ic-sm"></span></button>
          <button type="button" class="btn btn-outline" onclick="window.print()">Unduh Invoice
            <span data-icon="download" data-ic-class="ic-sm"></span></button>
        </div>
      </form>

      <div class="note-pill mt-24">
        <span data-icon="shield" data-ic-class="ic-sm"></span>
        Invoice ini juga dikirim ke <b>{{ $donasi->email }}</b>. Simpan sebagai bukti donasi.
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const dz = document.getElementById('dz'), input = document.getElementById('buktiInput');
  input.addEventListener('change', () => {
    const f = input.files[0];
    if (!f) return;
    dz.classList.add('has-file');
    document.getElementById('dzTitle').textContent = f.name;
    document.getElementById('dzSub').textContent = (f.size / 1024 / 1024).toFixed(1) + ' MB · siap dikirim';
  });
  document.querySelectorAll('[data-copy]').forEach(b => b.addEventListener('click', async () => {
    await navigator.clipboard.writeText(b.dataset.copy);
    const t = b.innerHTML;
    b.textContent = 'Nomor rekening tersalin';
    setTimeout(() => { b.innerHTML = t; location.reload(); }, 1600);
  }));
</script>
@endpush
