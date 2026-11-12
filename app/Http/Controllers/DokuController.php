<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Services\Doku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DokuController extends Controller
{
    /** Buat transaksi DOKU lalu redirect ke halaman Checkout (QRIS ada di sana). */
    public function pay(Donasi $donasi)
    {
        abort_unless($donasi->user_id === auth()->id() || auth()->user()?->isAdmin(), 403);

        if ($donasi->status === 'terverifikasi') {
            return redirect()->route('donasi.invoice', $donasi->kode)
                ->with('ok', 'Donasi ini sudah lunas.');
        }

        // pakai payment_url yang masih ada (hindari transaksi dobel)
        if ($donasi->payment_url) {
            return redirect()->away($donasi->payment_url);
        }

        $result = Doku::createPayment([
            'order' => [
                'amount' => (int) $donasi->total,
                'invoice_number' => $donasi->kode,
                'currency' => 'IDR',
                'callback_url' => route('doku.notify'),
                'callback_url_result' => route('donasi.invoice', $donasi->kode),
                'auto_redirect' => true,
                'line_items' => [[
                    'name' => 'Donasi '.$donasi->jumlah_pohon.' pohon — '.$donasi->kampanye->judul,
                    'quantity' => 1,
                    'price' => (int) $donasi->subtotal,
                    'sku' => 'POHON-'.$donasi->kampanye_id,
                ], [
                    'name' => 'Biaya administrasi',
                    'quantity' => 1,
                    'price' => (int) ($donasi->biaya_admin + $donasi->kode_unik),
                ]],
            ],
            'payment' => [
                'payment_due_date' => (int) config('services.doku.payment_due_minutes', 60),
                'payment_method_types' => ['QRIS'],
            ],
            'customer' => [
                'id' => 'USR-'.$donasi->user_id,
                'name' => $donasi->nama_donatur,
                'email' => $donasi->email,
                'phone' => '6280000000000',
                'country' => 'ID',
            ],
        ]);

        if (! $result['ok']) {
            return redirect()->route('donasi.invoice', $donasi->kode)
                ->withErrors(['doku' => 'Gagal membuat pembayaran DOKU: '.$result['error']]);
        }

        $donasi->update([
            'gateway' => 'doku',
            'gateway_ref' => $donasi->kode,
            'payment_url' => $result['url'],
        ]);

        return redirect()->away($result['url']);
    }

    /** HTTP Notification dari DOKU — verifikasi signature, lalu update status. */
    public function notify(Request $request)
    {
        $raw = $request->getContent();
        $payload = json_decode($raw, true) ?: [];
        $path = '/'.ltrim($request->path(), '/');

        if (! Doku::verifyNotification($request->headers->all(), $raw, $path)) {
            Log::warning('DOKU notify: signature tidak valid', ['path' => $path, 'ip' => $request->ip()]);

            return response()->json(['message' => ['UNAUTHORIZED']], 401);
        }

        $invoice = data_get($payload, 'order.invoice_number')
            ?? data_get($payload, 'transaction.inisiated_date')  // jaga-jaga format lain
            ?? ($payload['INVOICE'] ?? null);

        $donasi = $invoice ? Donasi::where('kode', $invoice)->first() : null;

        if (! $donasi) {
            Log::warning('DOKU notify: invoice tidak ditemukan', ['payload' => $payload]);

            return response()->json(['message' => ['NOT FOUND']], 404);
        }

        $status = strtoupper((string) (
            data_get($payload, 'transaction.status')
            ?? data_get($payload, 'order.status')
            ?? ($payload['TXNSTATUS'] ?? '')
        ));

        $lunas = in_array($status, ['SUCCESS', 'S', 'PAID', 'SETTLEMENT'], true);

        $donasi->update([
            'status' => $lunas ? 'terverifikasi' : 'ditolak',
            'paid_at' => $lunas ? now()->toDateTimeString() : $donasi->paid_at,
        ]);

        return response()->json(['message' => ['SUCCESS']], 200);
    }
}
