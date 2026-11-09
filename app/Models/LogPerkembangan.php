<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogPerkembangan extends Model
{
    protected $table = 'log_perkembangan';

    protected $fillable = ['kampanye_id', 'tanggal', 'kondisi', 'judul', 'catatan', 'foto'];

    protected $casts = ['tanggal' => 'date'];

    public function kampanye(): BelongsTo
    {
        return $this->belongsTo(Kampanye::class);
    }

    public function getKondisiLabelAttribute(): string
    {
        return $this->kondisi === 'perawatan' ? 'Perlu Perawatan' : 'Tumbuh Baik';
    }
}
