<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevTkksdDetail extends Model
{
    protected $table = 'monev_tkksd_details';

    protected $fillable = [
        'monev_id',
        'tkl_kepatuhan_pks',
        'tkl_koordinasi_mitra',
        'tkl_kesesuaian_anggaran',
        'tkl_temuan_pengawasan',
        'tkl_rekomendasi_lokus',
        'tkl_catatan',
    ];

    public function monev(): BelongsTo
    {
        return $this->belongsTo(Monev::class, 'monev_id');
    }
}
