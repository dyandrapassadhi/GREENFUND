<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Kampanye extends Model
{
    protected $table = 'kampanye';

    protected $fillable = [
        'judul', 'slug', 'kategori', 'lokasi', 'deskripsi',
        'target_pohon', 'harga_per_pohon', 'status', 'cover', 'penyelenggara', 'tenggat',
    ];

    protected $casts = ['tenggat' => 'date'];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (blank($m->slug)) {
                $m->slug = Str::slug($m->judul);
            }
        });
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LogPerkembangan::class)->latest('tanggal');
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class);
    }

    public function getTerkumpulAttribute(): int
    {
        return (int) $this->donasi()->where('status', 'terverifikasi')->sum('jumlah_pohon');
    }

    public function getPersenAttribute(): int
    {
        return $this->target_pohon > 0
            ? (int) round(min(100, $this->terkumpul / $this->target_pohon * 100))
            : 0;
    }

    public function getSisaHariAttribute(): ?int
    {
        return $this->tenggat ? max(0, (int) now()->startOfDay()->diffInDays($this->tenggat, false)) : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }
}
