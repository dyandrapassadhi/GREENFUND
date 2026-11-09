@extends('layouts.admin')
@section('title', 'Kelola Kampanye')

@section('body')
<div class="admin-head">
  <div>
    <nav class="crumbs" style="padding:0 0 8px">
      <a href="{{ route('admin.dashboard') }}">Dashboard</a><span class="sep">/</span><span class="current">Kelola Kampanye</span>
    </nav>
    <h1 class="h2" style="font-size:30px;line-height:38px">Kelola Kampanye</h1>
    <p class="small mt-8">Tambah, ubah, dan hapus kampanye penghijauan yang tampil di halaman publik.</p>
  </div>
  <button class="btn btn-primary" data-open="modal-kampanye">
    <span data-icon="plus" data-ic-class="ic-sm"></span>Buat Kampanye Baru</button>
</div>

<div class="card" style="overflow:hidden">
  <div class="card-head">
    <h2 class="h5">{{ $kampanye->total() }} kampanye</h2>
    <span class="small" style="font-size:12px">Diperbarui {{ now()->translatedFormat('d M Y') }}</span>
  </div>
  <table class="table">
    <thead><tr><th>Kampanye</th><th>Kategori</th><th>Progres</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
    <tbody>
      @forelse($kampanye as $k)
        @php $persen = $k->target_pohon ? min(100, round(($k->terkumpul ?? 0) / $k->target_pohon * 100)) : 0; @endphp
        <tr>
          <td data-l="Kampanye"><div class="cell-stack"><b>{{ $k->judul }}</b><small>{{ $k->lokasi }}</small></div></td>
          <td data-l="Kategori"><span class="badge badge-amber">{{ $k->kategori }}</span></td>
          <td data-l="Progres" style="min-width:170px">
            <div class="progress"><i style="width:{{ $persen }}%"></i></div>
            <small class="small num" style="font-size:11.5px">{{ number_format($k->terkumpul ?? 0, 0, ',', '.') }} / {{ number_format($k->target_pohon, 0, ',', '.') }} &middot; {{ $persen }}%</small>
          </td>
          <td data-l="Status"><span class="badge {{ $k->status === 'aktif' ? 'badge-green' : 'badge-slate' }}"><span class="dot"></span>{{ $k->status_label }}</span></td>
          <td data-l="Aksi" class="text-right" style="white-space:nowrap">
            <a href="{{ route('katalog.show', $k) }}" class="btn-icon" style="width:32px;height:32px;display:inline-flex" title="Lihat di situs publik" aria-label="Lihat {{ $k->judul }}">
              <span data-icon="eye" data-ic-class="ic-sm"></span></a>
            <button class="btn-icon" style="width:32px;height:32px;display:inline-flex" title="Edit" aria-label="Edit {{ $k->judul }}"
              data-open="modal-kampanye" data-fill="{{ json_encode($k->only(['id','judul','kategori','lokasi','target_pohon','harga_per_pohon','status','deskripsi'])) }}">
              <span data-icon="pencil" data-ic-class="ic-sm"></span></button>
            <form method="post" action="{{ route('admin.kampanye.destroy', $k) }}" style="display:inline" onsubmit="return confirm('Hapus kampanye {{ $k->judul }}?')">
              @csrf @method('DELETE')
              <button class="btn-icon" style="width:32px;height:32px;display:inline-flex;color:var(--danger)" title="Hapus" aria-label="Hapus {{ $k->judul }}">
                <span data-icon="x" data-ic-class="ic-sm"></span></button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5">
          <div class="empty">
            <span class="chip"><span data-icon="layers" data-ic-class="ic-lg"></span></span>
            <h3 class="h5">Belum ada kampanye</h3>
            <p class="small">Buat kampanye pertama agar tampil di halaman publik.</p>
            <button class="btn btn-primary btn-sm" data-open="modal-kampanye">Buat kampanye</button>
          </div>
        </td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@if($kampanye->hasPages())<div class="mt-16">{{ $kampanye->links() }}</div>@endif

{{-- Modal create/edit --}}
<div class="mask" id="modal-kampanye" hidden>
  <div class="modal">
    <form method="post" action="{{ route('admin.kampanye.store') }}" id="formKampanye">
      @csrf
      <input type="hidden" name="_method" value="POST" id="fMethod">
      <div class="card-head" style="border-bottom:1px solid var(--border)">
        <h2 class="h5" id="modalTitle">Buat Kampanye Baru</h2>
        <button type="button" class="btn-icon" style="width:32px;height:32px;border:none" data-close><span data-icon="x" data-ic-class="ic-sm"></span></button>
      </div>
      <div style="padding:22px">
        <p class="small mb-16">Lengkapi data kampanye. Kampanye tampil di halaman publik setelah disimpan.</p>
        <div class="grid-3" style="gap:14px">
          <div class="field" style="grid-column:1/-1">
            <label class="label">Judul Kampanye</label>
            <div class="input"><input name="judul" required placeholder="Hutan Mangrove Pesisir Demak"></div>
          </div>
          <div class="field">
            <label class="label">Target Pohon</label>
            <div class="input"><input type="number" name="target_pohon" min="1" value="10000" required></div>
          </div>
          <div class="field">
            <label class="label">Harga per Pohon</label>
            <div class="input"><input type="number" name="harga_per_pohon" min="1000" step="500" value="25000" required></div>
          </div>
          <div class="field">
            <label class="label">Kategori</label>
            <div class="input select"><select name="kategori">
              @foreach(['Penghijauan','Mangrove','Restorasi DAS','Gambut'] as $kat)<option>{{ $kat }}</option>@endforeach
            </select></div>
          </div>
          <div class="field" style="grid-column:1/-1">
            <label class="label">Lokasi Penanaman</label>
            <div class="input"><span data-icon="map-pin" data-ic-class="ic-sm" style="color:var(--muted)"></span>
              <input name="lokasi" required placeholder="Demak, Jawa Tengah"></div>
          </div>
          <div class="field" style="grid-column:1/-1">
            <label class="label">Status Kampanye</label>
            <div class="radio-row">
              @foreach(['aktif' => 'Aktif', 'terpenuhi' => 'Terpenuhi', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $val => $txt)
                <label><input type="radio" name="status" value="{{ $val }}" @checked($val === 'aktif')>{{ $txt }}</label>
              @endforeach
            </div>
          </div>
          <div class="field" style="grid-column:1/-1">
            <label class="label">Deskripsi</label>
            <textarea class="textarea" name="deskripsi" maxlength="2000" rows="4"
              placeholder="Kampanye ini menanam bibit mangrove di pesisir untuk menahan abrasi..."></textarea>
          </div>
          <div class="field" style="grid-column:1/-1">
            <label class="label">Gambar Cover</label>
            <div class="dropzone" style="padding:22px">
              <span style="width:40px;height:40px;border-radius:12px;background:#fff;border:1px dashed var(--leaf);display:flex;align-items:center;justify-content:center;color:var(--emerald)">
                <span data-icon="image" data-ic-class="ic-sm"></span></span>
              <b style="font-size:13.5px">Unggah cover kampanye</b>
              <span class="tiny">PNG atau JPG, maks. 5 MB &middot; rasio 16:9</span>
            </div>
          </div>
        </div>
      </div>
      <div class="card-head" style="border-top:1px solid var(--border);justify-content:flex-end">
        <button type="button" class="btn btn-outline btn-sm" data-close>Batal</button>
        <button class="btn btn-primary btn-sm">Simpan Kampanye <span data-icon="check" data-ic-class="ic-sm"></span></button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const mask = document.getElementById('modal-kampanye');
  const form = document.getElementById('formKampanye');
  const method = document.getElementById('fMethod');
  const title = document.getElementById('modalTitle');
  const base = @json(route('admin.kampanye.store'));

  document.querySelectorAll('[data-open="modal-kampanye"]').forEach(b => b.addEventListener('click', () => {
    const data = b.dataset.fill ? JSON.parse(b.dataset.fill) : null;
    form.reset();
    if (data) {
      title.textContent = 'Edit Kampanye';
      form.action = `${base}/${data.id}`;
      method.value = 'PUT';
      for (const [k, v] of Object.entries(data)) {
        const el = form.querySelector(`[name="${k}"]`);
        if (!el) continue;
        if (el.type === 'radio') form.querySelector(`[name="${k}"][value="${v}"]`)?.click();
        else el.value = v ?? '';
      }
    } else {
      title.textContent = 'Buat Kampanye Baru';
      form.action = base;
      method.value = 'POST';
      form.querySelector('[name=target_pohon]').value = 10000;
      form.querySelector('[name=harga_per_pohon]').value = 25000;
    }
    mask.hidden = false;
  }));
  mask.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => mask.hidden = true));
  mask.addEventListener('click', e => { if (e.target === mask) mask.hidden = true; });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') mask.hidden = true; });
</script>
@endpush
