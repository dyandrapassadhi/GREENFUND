<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donasi;
use App\Models\Kampanye;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sum = fn () => (int) Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon');

        $stat = [
            'kampanye_aktif' => Kampanye::where('status', 'aktif')->count(),
            'pohon' => $sum(),
            'pending' => Donasi::where('status', 'pending')->count(),
            'donatur' => Donasi::distinct('email')->count('email'),
        ];

        $donasi = Donasi::with('kampanye')->latest('id')->take(8)->get();

        $progres = Kampanye::withSum(['donasi as terkumpul' => fn ($q) => $q->where('status', 'terverifikasi')], 'jumlah_pohon')
            ->where('status', 'aktif')->take(3)->get();

        return view('admin.dashboard', [
            'stat' => $stat,
            'donasi' => $donasi,
            'progres' => $progres,
            'totalPohon' => $stat['pohon'],
            'totalKampanye' => $stat['kampanye_aktif'],
        ]);
    }

    public function donatur()
    {
        $rows = Donasi::query()
            ->select('email', 'nama_donatur', DB::raw('COUNT(*) AS transaksi'), DB::raw('SUM(jumlah_pohon) AS pohon'), DB::raw('SUM(total) AS nilai'), DB::raw('MAX(created_at) AS terakhir'))
            ->groupBy('email', 'nama_donatur')->orderByDesc('nilai')->paginate(12);

        return view('admin.donatur', [
            'rows' => $rows,
            'totalPohon' => (int) Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'totalKampanye' => Kampanye::where('status', 'aktif')->count(),
        ]);
    }

    public function users()
    {
        return User::all();
    }
}
