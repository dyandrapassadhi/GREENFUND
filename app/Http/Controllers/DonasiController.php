<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Kampanye;
use App\Services\Doku;
use App\Services\Qris;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DonasiController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'kampanye_id' => ['required', 'exists:kampanye,id'],
            'jumlah_pohon' => ['required', 'integer', 'min:1', 'max:100000'],
        ], [
            'jumlah_pohon.min' => 'Minimal 1 pohon per donasi.',
        ]);

        $user = $request->user();
        $kampanye = Kampanye::findOrFail($data['kampanye_id']);
        $subtotal = $data['jumlah_pohon'] * $kampanye->harga_per_pohon;
        $unik = random_int(100, 999);

        $donasi = Donasi::create([
            'kode' => Donasi::kodeBaru(),
            'kampanye_id' => $kampanye->id,
            'user_id' => $user->id,
            'nama_donatur' => $user->name,
            'email' => $user->email,
            'jumlah_pohon' => $data['jumlah_pohon'],
            'subtotal' => $subtotal,
            'biaya_admin' => 5000,
            'kode_unik' => $unik,
            'total' => $subtotal + 5000 + $unik,
            'status' => 'pending',
            'tenggat_bayar' => now()->addDay()->setTime(23, 59),
        ]);

        return redirect()->route('donasi.invoice', $donasi->kode);
    }

    public function invoice(Donasi $donasi)
    {
        return view('public.invoice', [
            'donasi' => $donasi,
            'qrisPayload' => Qris::forAmount($donasi->total),
            'dokuReady' => Doku::enabled(),
        ]);
    }

    /** PNG QRIS dinamis — nominal sudah terisi otomatis sesuai total invoice. */
    public function qris(Donasi $donasi): Response
    {
        $payload = Qris::forAmount($donasi->total);

        abort_if($payload === null, 404, 'QRIS belum dikonfigurasi.');

        $result = (new Builder(
            writer: new PngWriter,
            data: $payload,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 440,
            margin: 8,
        ))->build();

        return response($result->getString(), 200, [
            'Content-Type' => $result->getMimeType(),
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function uploadBukti(Request $request, Donasi $donasi)
    {
        $request->validate([
            'bukti' => ['required', 'image', 'max:5120'],
        ], [
            'bukti.required' => 'Pilih file bukti transfer terlebih dahulu.',
            'bukti.image' => 'File harus berupa gambar PNG atau JPG.',
            'bukti.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $path = $request->file('bukti')->store('bukti', 'public');
        $donasi->update(['bukti' => $path]);

        return redirect()->route('donasi.invoice', $donasi->kode)
            ->with('ok', 'Bukti pembayaran terkirim. Menunggu verifikasi admin maks. 1×24 jam.');
    }
}
