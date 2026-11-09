<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Kampanye;
use Illuminate\Http\Request;

class DonasiController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $donasi = Donasi::with('kampanye')
            ->when($status && $status !== 'semua', fn ($q) => $q->where('status', $status))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('kode', 'like', "%{$term}%")
                ->orWhere('nama_donatur', 'like', "%{$term}%")))
            ->latest('id')->paginate(15)->withQueryString();

        return view('admin.donasi.index', [
            'donasi' => $donasi,
            'status' => $status ?: 'semua',
            'totalPohon' => (int) Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'totalKampanye' => Kampanye::where('status', 'aktif')->count(),
        ]);
    }

    public function updateStatus(Request $request, Donasi $donasi)
    {
        $data = $request->validate(['status' => ['required', 'in:pending,terverifikasi,ditolak']]);
        $donasi->update($data);

        return back()->with('ok', "Donasi {$donasi->kode} ditandai: {$donasi->fresh()->status_label}.");
    }
}
