<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $donasi = Donasi::with('kampanye')
            ->where('user_id', $user->id)
            ->latest('id')
            ->get();

        $ringkas = [
            'total_donasi' => $donasi->count(),
            'pohon' => (int) $donasi->where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'rupiah' => (int) $donasi->where('status', 'terverifikasi')->sum('total'),
            'kampanye' => $donasi->pluck('kampanye_id')->unique()->count(),
            'pending' => $donasi->where('status', 'pending')->count(),
        ];

        return view('public.profil', compact('user', 'donasi', 'ringkas'));
    }

    /** Detail satu donasi milik user (halaman tracking). */
    public function donasi(Request $request, Donasi $donasi)
    {
        abort_unless($donasi->user_id === $request->user()->id, 403);

        $donasi->load('kampanye.logs');

        return view('public.profil-donasi', [
            'user' => $request->user(),
            'donasi' => $donasi,
        ]);
    }
}
