<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kampanye;
use App\Models\LogPerkembangan;
use Illuminate\Http\Request;

class LogPerkembanganController extends Controller
{
    public function index(Request $request)
    {
        $kampanyeList = Kampanye::orderBy('judul')->get();
        $selected = Kampanye::find($request->query('kampanye')) ?? $kampanyeList->first();
        $logs = $selected ? $selected->logs()->take(20)->get() : collect();

        return view('admin.log.index', [
            'kampanyeList' => $kampanyeList,
            'selected' => $selected,
            'logs' => $logs,
            'totalPohon' => (int) \App\Models\Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'totalKampanye' => Kampanye::where('status', 'aktif')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kampanye_id' => ['required', 'exists:kampanye,id'],
            'tanggal' => ['required', 'date'],
            'kondisi' => ['required', 'in:baik,perawatan'],
            'judul' => ['required', 'string', 'max:140'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'max:5120'],
        ], [
            'kampanye_id.required' => 'Pilih kampanye terlebih dahulu.',
            'tanggal.required' => 'Tanggal log wajib diisi.',
            'judul.required' => 'Judul log wajib diisi.',
            'foto.image' => 'Foto harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('log', 'public');
        }

        LogPerkembangan::create($data);

        return redirect()->route('admin.log.index', ['kampanye' => $data['kampanye_id']])
            ->with('ok', 'Log perkembangan tersimpan dan tampil di halaman kampanye.');
    }

    public function destroy(LogPerkembangan $log)
    {
        $log->delete();

        return back()->with('ok', 'Log perkembangan dihapus.');
    }
}
