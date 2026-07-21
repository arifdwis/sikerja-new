<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevPemohonDetail extends Model
{
    protected $table = 'monev_pemohon_details';

    protected $fillable = [
        'monev_id',
        'pmh_realisasi_kegiatan',
        'pmh_kesesuaian_output',
        'pmh_pemanfaatan_hasil',
        'pmh_kendala_lapangan',
        'pmh_keberlanjutan',
        'pmh_file_laporan',
    ];

    public function monev(): BelongsTo
    {
        return $this->belongsTo(Monev::class, 'monev_id');
    }
}
