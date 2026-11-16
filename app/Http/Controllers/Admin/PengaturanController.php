<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('admin.pengaturan', [
            'heroImage' => Storage::disk('public')->exists('hero/hero.jpg'),
            'totalPohon' => (int) \App\Models\Donasi::where('status', 'terverifikasi')->sum('jumlah_pohon'),
            'totalKampanye' => \App\Models\Kampanye::where('status', 'aktif')->count(),
        ]);
    }

    public function uploadHero(Request $request)
    {
        $request->validate([
            'hero' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ], [
            'hero.required' => 'Pilih foto terlebih dahulu.',
            'hero.image' => 'File harus berupa gambar.',
            'hero.mimes' => 'Format harus PNG, JPG, atau WEBP.',
            'hero.max' => 'Ukuran maksimal 5 MB.',
        ]);

        if (Storage::disk('public')->exists('hero/hero.jpg')) {
            Storage::disk('public')->delete('hero/hero.jpg');
        }

        $request->file('hero')->storeAs('hero', 'hero.jpg', 'public');

        return back()->with('ok', 'Foto hero berhasil diperbarui.');
    }

    public function hapusHero()
    {
        Storage::disk('public')->delete('hero/hero.jpg');

        return back()->with('ok', 'Foto hero dihapus, kembali ke tampilan default.');
    }
}
