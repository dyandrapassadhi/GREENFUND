<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donasi extends Model
{
    protected $table = 'donasi';

    protected $fillable = [
        'kode', 'kampanye_id', 'user_id', 'nama_donatur', 'email', 'jumlah_pohon',
        'subtotal', 'biaya_admin', 'kode_unik', 'total', 'status', 'bukti', 'tenggat_bayar',
        'gateway', 'gateway_ref', 'payment_url', 'paid_at',
    ];

    protected $casts = ['tenggat_bayar' => 'datetime'];

    public const STATUS_LABEL = [
        'pending' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'ditolak' => 'Ditolak',
    ];

    public function kampanye(): BelongsTo
    {
        return $this->belongsTo(Kampanye::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
            default => 'badge-amber',
        };
    }

    public static function kodeBaru(): string
    {
        $last = static::whereYear('created_at', now()->year)->count() + 1;

        return sprintf('INV-%s-%05d', now()->year, 8400 + $last);
    }
}
