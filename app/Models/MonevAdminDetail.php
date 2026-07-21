<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevAdminDetail extends Model
{
    protected $table = 'monev_admin_details';

    protected $fillable = [
        'monev_id',
        'kesesuaian_tujuan',
        'ketepatan_waktu',
        'kontribusi_mitra',
        'tingkat_koordinasi',
        'capaian_indikator',
        'dampak_pelaksanaan',
        'inovasi_manfaat',
        'kelengkapan_dokumen',
        'pelaporan_berkala',
        'kendala_administrasi',
        'relevansi_kebutuhan',
        'rekomendasi_lanjutan',
        'saran_rekomendasi',
        'file_bukti',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function monev(): BelongsTo
    {
        return $this->belongsTo(Monev::class, 'monev_id');
    }
}
