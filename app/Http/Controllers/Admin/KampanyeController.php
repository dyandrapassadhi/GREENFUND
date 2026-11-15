<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kampanye;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KampanyeController extends Controller
{
    public function index()
    {
        $kampanye = Kampanye::withSum(['donasi as terkumpul' => fn ($q) => $q->where('status', 'terverifikasi')], 'jumlah_pohon')
            ->latest('id')->paginate(12);

        return view('admin.kampanye.index', [
            'kampanye' => $kampanye,
            'totalPohon' => (int) $kampanye->sum(fn ($k) => $k->terkumpul ?? 0),
            'totalKampanye' => Kampanye::where('status', 'aktif')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['judul']).'-'.Str::lower(Str::random(4));

        if ($request->hasFile('cover')) {
            $data['cover'] = $request->file('cover')->store('cover', 'public');
        }

        Kampanye::create($data);

        return back()->with('ok', 'Kampanye baru berhasil disimpan dan tampil di halaman publik.');
    }

    public function update(Request $request, Kampanye $kampanye)
    {
        $data = $this->validated($request);

        if ($request->hasFile('cover')) {
            if ($kampanye->cover) {
                Storage::disk('public')->delete($kampanye->cover);
            }
            $data['cover'] = $request->file('cover')->store('cover', 'public');
        }

        $kampanye->update($data);

        return back()->with('ok', 'Kampanye berhasil diperbarui.');
    }

    public function destroy(Kampanye $kampanye)
    {
        if ($kampanye->cover) {
            Storage::disk('public')->delete($kampanye->cover);
        }

        $kampanye->delete();

        return back()->with('ok', 'Kampanye dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:120'],
            'kategori' => ['required', 'in:Penghijauan,Mangrove,Restorasi DAS,Gambut'],
            'lokasi' => ['required', 'string', 'max:120'],
            'target_pohon' => ['required', 'integer', 'min:1'],
            'harga_per_pohon' => ['required', 'integer', 'min:1000'],
            'status' => ['required', 'in:aktif,terpenuhi,selesai,dibatalkan'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'cover' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ], [
            'judul.required' => 'Judul kampanye wajib diisi.',
            'kategori.required' => 'Pilih kategori kampanye.',
            'lokasi.required' => 'Lokasi penanaman wajib diisi.',
            'target_pohon.min' => 'Target pohon minimal 1.',
            'harga_per_pohon.min' => 'Harga per pohon minimal Rp 1.000.',
            'cover.image' => 'Cover harus berupa gambar.',
            'cover.mimes' => 'Format cover harus PNG, JPG, atau WEBP.',
            'cover.max' => 'Ukuran cover maksimal 5 MB.',
        ]);
    }
}
