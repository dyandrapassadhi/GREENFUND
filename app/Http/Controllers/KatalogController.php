<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Kampanye;
use Illuminate\Http\Request;

class KatalogController extends Controller
{
    private function stats(): array
    {
        return [
            'totalPohon' => (int) Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'totalKampanye' => Kampanye::count(),
            'aktif' => Kampanye::where('status', 'aktif')->count(),
        ];
    }

    public function home()
    {
        $stats = $this->stats();
        $unggulan = Kampanye::withSum(['donasi as terkumpul' => fn ($q) => $q->where('status', 'terverifikasi')], 'jumlah_pohon')
            ->where('status', 'aktif')->take(3)->get();

        // Foto hero: taruh file di public/img/hero.jpg (atau .png/.webp)
        $heroFile = collect(['hero.jpg', 'hero.jpeg', 'hero.png', 'hero.webp'])
            ->first(fn ($f) => is_file(public_path('img/'.$f)));

        return view('public.home', [
            'stats' => $stats,
            'unggulan' => $unggulan,
            'heroImage' => $heroFile ? asset('img/'.$heroFile) : null,
        ]);
    }

    public function index(Request $request)
    {
        $kategori = $request->query('kategori');
        $kampanye = Kampanye::withSum(['donasi as terkumpul' => fn ($q) => $q->where('status', 'terverifikasi')], 'jumlah_pohon')
            ->when($kategori && $kategori !== 'Semua', fn ($q) => $q->where('kategori', $kategori))
            ->orderByDesc('id')->paginate(9)->withQueryString();

        return view('public.katalog', [
            'kampanye' => $kampanye,
            'kategori' => $kategori ?: 'Semua',
            'stats' => $this->stats(),
        ]);
    }

    public function show(Kampanye $kampanye)
    {
        $kampanye->load(['logs' => fn ($q) => $q->latest('tanggal')->take(6)]);
        $terkumpul = (int) $kampanye->donasi()->where('status', 'terverifikasi')->sum('jumlah_pohon');
        $donatur = (int) $kampanye->donasi()->where('status', 'terverifikasi')->count();

        return view('public.detail', compact('kampanye', 'terkumpul', 'donatur'));
    }

    public function tentang()
    {
        return view('public.tentang', ['stats' => $this->stats()]);
    }
}
