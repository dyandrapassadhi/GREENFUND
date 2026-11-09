<?php

namespace App\Services;

/**
 * QRIS (EMVCo) static → dynamic converter.
 * Only mutates tag 01 (static→dynamic), injects tag 54 (amount), recomputes tag 63 CRC16.
 */
class Qris
{
    public static function crc16(string $payload): string
    {
        $crc = 0xFFFF;
        for ($i = 0, $n = strlen($payload); $i < $n; $i++) {
            $crc ^= ord($payload[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) & 0xFFFF : ($crc << 1) & 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    /** @return array<int, array{0:string,1:string}> */
    public static function parse(string $payload): array
    {
        $payload = self::stripCrc($payload);
        $out = [];
        $i = 0;
        $n = strlen($payload);

        while ($i + 4 <= $n) {
            $tag = substr($payload, $i, 2);
            $len = (int) substr($payload, $i + 2, 2);
            if (! ctype_digit($tag) || ! ctype_digit(substr($payload, $i + 2, 2)) || $i + 4 + $len > $n) {
                throw new \InvalidArgumentException('Payload QRIS tidak valid.');
            }
            $out[] = [$tag, substr($payload, $i + 4, $len)];
            $i += 4 + $len;
        }

        return $out;
    }

    public static function stripCrc(string $payload): string
    {
        $pos = strrpos($payload, '6304');

        return $pos === false ? trim($payload) : substr($payload, 0, $pos);
    }

    /** Inject nominal ke QRIS statis dan hitung ulang CRC. */
    public static function dynamic(string $static, int $amount): string
    {
        $tags = self::parse($static);
        $amount = (string) $amount;
        $result = '';
        $injected = false;

        foreach ($tags as [$tag, $value]) {
            if ($tag === '63') {
                continue;
            }
            if ($tag === '01') {
                $value = '12'; // 11 = static, 12 = dynamic
            }
            if ($tag === '54') {
                $value = $amount;
                $injected = true;
            }
            if ($tag === '63') {
                continue;
            }
            // tag 54 harus berada setelah 53 (currency) dan sebelum 58 (country)
            if (! $injected && $tag === '58') {
                $result .= self::tlv('54', $amount);
                $injected = true;
            }
            $result .= self::tlv($tag, $value);
        }

        if (! $injected) {
            $result .= self::tlv('54', $amount);
        }

        $result .= '6304';

        return $result.self::crc16($result);
    }

    public static function tlv(string $tag, string $value): string
    {
        return $tag.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    /** Gabungkan template statis (dari config) dengan nominal; null kalau template belum diisi. */
    public static function forAmount(int $amount): ?string
    {
        $template = trim((string) config('services.qris.static'));

        if ($template === '' || strlen($template) < 20) {
            return null;
        }

        return self::dynamic($template, $amount);
    }
}
