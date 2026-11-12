<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * DOKU Jokul Checkout (non-SNAP, HMAC-SHA256).
 * Hanya untuk membuat payment.url; QRIS ditampilkan oleh halaman Checkout DOKU.
 */
class Doku
{
    private const TARGET = '/checkout/v1/payment';

    public static function enabled(): bool
    {
        return filled(config('services.doku.client_id')) && filled(config('services.doku.secret_key'));
    }

    public static function baseUrl(): string
    {
        return config('services.doku.sandbox')
            ? 'https://api-sandbox.doku.com'
            : 'https://api.doku.com';
    }

    /** Digest = base64(sha256(jsonBody)) */
    public static function digest(string $json): string
    {
        return base64_encode(hash('sha256', $json, true));
    }

    /**
     * Signature non-SNAP: HMACSHA256 dari komponen per baris.
     * Client-Id / Request-Id / Request-Timestamp / Request-Target / Digest
     */
    public static function signature(
        string $clientId,
        string $requestId,
        string $timestamp,
        string $target,
        ?string $digest,
        string $secret
    ): string {
        $components = [
            'Client-Id:'.$clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$target,
        ];

        if ($digest !== null) {
            $components[] = 'Digest:'.$digest;
        }

        $raw = implode("\n", $components);

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $raw, $secret, true));
    }

    /**
     * Buat payment URL di DOKU Checkout.
     *
     * @param  array  $payload  body sesuai kontrak DOKU (order, payment, customer, ...)
     * @return array{ok:bool, url:?string, raw:array, error:?string}
     */
    public static function createPayment(array $payload): array
    {
        if (! self::enabled()) {
            return ['ok' => false, 'url' => null, 'raw' => [], 'error' => 'DOKU belum dikonfigurasi.'];
        }

        $clientId = (string) config('services.doku.client_id');
        $secret = (string) config('services.doku.secret_key');
        $requestId = (string) Str::uuid();
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $headers = [
            'Client-Id' => $clientId,
            'Request-Id' => $requestId,
            'Request-Timestamp' => $timestamp,
            'Signature' => self::signature($clientId, $requestId, $timestamp, self::TARGET, self::digest($body), $secret),
            'Content-Type' => 'application/json',
        ];

        try {
            $res = Http::withHeaders($headers)
                ->timeout(30)
                ->withBody($body, 'application/json')
                ->post(self::baseUrl().self::TARGET);
        } catch (\Throwable $e) {
            return ['ok' => false, 'url' => null, 'raw' => [], 'error' => 'Gagal menghubungi DOKU: '.$e->getMessage()];
        }

        $json = $res->json() ?? [];

        if (! $res->successful()) {
            $msg = $json['message'] ?? $json['error']['message'] ?? 'HTTP '.$res->status();

            return [
                'ok' => false,
                'url' => null,
                'raw' => $json,
                'error' => is_array($msg) ? implode(', ', $msg) : (string) $msg,
            ];
        }

        $url = data_get($json, 'response.payment.url');

        return [
            'ok' => (bool) $url,
            'url' => $url,
            'raw' => $json,
            'error' => $url ? null : 'DOKU tidak mengembalikan payment.url.',
        ];
    }

    /**
     * Verifikasi notifikasi HTTP DOKU ke endpoint kita.
     * Digest dihitung dari raw body; Request-Target = path notification URL kita.
     */
    public static function verifyNotification(array $headers, string $rawBody, string $path): bool
    {
        // Header Symfony bisa berupa string atau array (multi-value) — normalisasi dulu.
        $h = fn (string $key) => collect($headers)
            ->filter(fn ($v, $k) => strcasecmp($k, $key) === 0)
            ->map(fn ($v) => is_array($v) ? ($v[0] ?? '') : $v)
            ->first() ?? '';

        $clientId = (string) $h('Client-Id');
        $requestId = (string) $h('Request-Id');
        $timestamp = (string) $h('Request-Timestamp');
        $signature = (string) $h('Signature');

        if ($clientId === '' || $requestId === '' || $timestamp === '' || $signature === '') {
            return false;
        }

        if ($clientId !== (string) config('services.doku.client_id')) {
            return false;
        }

        $expected = self::signature(
            $clientId,
            $requestId,
            $timestamp,
            $path,
            self::digest($rawBody),
            (string) config('services.doku.secret_key')
        );

        return hash_equals($expected, $signature);
    }

    /** WORDS untuk notifikasi QRIS DOKU: sha1(ISSUERID + TXNDATE + MERCHANTPAN + INVOICE + sharedKey) */
    public static function qrisWords(array $p): string
    {
        $raw = ($p['ISSUERID'] ?? '').($p['TXNDATE'] ?? '').($p['MERCHANTPAN'] ?? '')
            .($p['INVOICE'] ?? '').(string) config('services.doku.shared_key', '');

        return sha1($raw);
    }
}
