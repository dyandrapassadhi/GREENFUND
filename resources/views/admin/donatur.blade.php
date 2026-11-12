@extends('layouts.admin')
@section('title', 'Donatur')

@section('body')
<div class="admin-head">
  <div>
    <h1 class="h2" style="font-size:30px;line-height:38px">Donatur</h1>
    <p class="small mt-8">Daftar donatur teragregasi per email beserta total dukungannya.</p>
  </div>
</div>

<div class="card" style="overflow:hidden">
  <div class="card-head"><h2 class="h5">{{ $rows->total() }} donatur</h2>
    <span class="small" style="font-size:12px">Diperbarui {{ now()->translatedFormat('d M Y') }}</span></div>
  <table class="table">
    <thead><tr><th>Donatur</th><th>Transaksi</th><th>Pohon</th><th>Nilai Donasi</th><th>Terakhir</th><th class="text-right">Aksi</th></tr></thead>
    <tbody>
      @forelse($rows as $r)
        <tr>
          <td data-l="Donatur">
            <div class="row gap-12">
              <span class="avatar sm">{{ collect(explode(' ', $r->nama_donatur))->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('') }}</span>
              <div class="cell-stack">{{ $r->nama_donatur }}<small>{{ $r->email }}</small></div>
            </div>
          </td>
          <td data-l="Transaksi" class="num">{{ $r->transaksi }}</td>
          <td data-l="Pohon" class="num">{{ number_format($r->pohon, 0, ',', '.') }}</td>
          <td data-l="Nilai Donasi" class="num">Rp {{ number_format($r->nilai, 0, ',', '.') }}</td>
          <td data-l="Terakhir" class="small">{{ \Illuminate\Support\Carbon::parse($r->terakhir)->translatedFormat('d M Y') }}</td>
          <td data-l="Aksi" class="text-right" style="white-space:nowrap">
            <button class="btn-icon" style="width:30px;height:30px;color:var(--danger)"
                    title="Hapus donatur" aria-label="Hapus donatur {{ $r->nama_donatur }}"
                    data-open="modal-hapus" data-email="{{ $r->email }}" data-nama="{{ $r->nama_donatur }}" data-transaksi="{{ $r->transaksi }}">
              <span data-icon="x" data-ic-class="ic-sm"></span></button>
          </td>
        </tr>
      @empty
        <tr><td colspan="6">
          <div class="empty">
            <span class="chip"><span data-icon="users" data-ic-class="ic-lg"></span></span>
            <h3 class="h5">Belum ada donatur</h3>
            <p class="small">Donatur muncul setelah ada donasi masuk.</p>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@if($rows->hasPages())<div class="mt-16">{{ $rows->links() }}</div>@endif

<div class="mask" id="modal-hapus" hidden>
  <div class="modal" style="max-width:460px">
    <form method="post" id="formHapus">
      @csrf @method('DELETE')
      <div class="card-head" style="border-bottom:1px solid var(--border)">
        <h2 class="h5">Hapus donatur</h2>
        <button type="button" class="btn-icon" style="width:32px;height:32px;border:none" data-close>
          <span data-icon="x" data-ic-class="ic-sm"></span></button>
      </div>
      <div style="padding:22px">
        <div class="note-pill" style="background:var(--danger-soft);color:var(--danger)">
          <span data-icon="shield" data-ic-class="ic-sm"></span>
          Tindakan ini menghapus permanen akun, semua donasi, dan bukti transfer.
        </div>
        <p class="small mt-16" id="hapusInfo"></p>
        <div class="field mt-16">
          <label class="label">Ketik <b>HAPUS</b> untuk konfirmasi</label>
          <div class="input"><input name="konfirmasi" placeholder="HAPUS" autocomplete="off" required></div>
        </div>
        @error('konfirmasi')<p class="err mt-8">{{ $message }}</p>@enderror
      </div>
      <div class="card-head" style="border-top:1px solid var(--border);justify-content:flex-end">
        <button type="button" class="btn btn-outline btn-sm" data-close>Batal</button>
        <button class="btn btn-primary btn-sm" style="background:var(--danger)">Hapus permanen</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const mask = document.getElementById('modal-hapus');
  const form = document.getElementById('formHapus');
  const base = @json(url('/admin/donatur'));

  document.querySelectorAll('[data-open="modal-hapus"]').forEach(b => b.addEventListener('click', () => {
    form.action = `${base}/${encodeURIComponent(b.dataset.email)}`;
    document.getElementById('hapusInfo').innerHTML =
      `<b>${b.dataset.nama}</b> (${b.dataset.email}) dengan <b>${b.dataset.transaksi}</b> donasi akan dihapus.`;
    form.querySelector('[name=konfirmasi]').value = '';
    mask.hidden = false;
    setTimeout(() => form.querySelector('[name=konfirmasi]').focus(), 80);
  }));
  mask.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => mask.hidden = true));
  mask.addEventListener('click', e => { if (e.target === mask) mask.hidden = true; });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') mask.hidden = true; });
</script>
@endpush
