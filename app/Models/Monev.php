<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Monev extends Model
{
    protected $table = 'monevs';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'id_pemohon',
        'kode_monev',
        'tanggal_evaluasi',
        // Evaluasi Pelaksanaan
        'kesesuaian_tujuan',
        'ketepatan_waktu',
        'kontribusi_mitra',
        'tingkat_koordinasi',
        // Capaian & Dampak
        'capaian_indikator',
        'dampak_pelaksanaan',
        'inovasi_manfaat',
        // Administrasi
        'kelengkapan_dokumen',
        'pelaporan_berkala',
        'kendala_administrasi',
        // Rekomendasi
        'relevansi_kebutuhan',
        'rekomendasi_lanjutan',
        'saran_rekomendasi',
        // Bukti
        'file_bukti',
        // Rating (Req 13)
        'rating',
        // Tipe: reguler / manual (Req 16)
        'tipe',
        // Submitter role + user (Req baru: pemohon/admin/tkksd_lokus monev mandiri-paralel)
        'submitter_role',
        'submitter_user_id',
        // TKKSD Lokus review (Req 11)
        'id_tkksd_lokus',
        'tkksd_approved_at',
        'tkksd_catatan',
        // Status
        'status',
        'catatan_admin',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'tanggal_evaluasi' => 'date',
        'reviewed_at' => 'datetime',
        'tkksd_approved_at' => 'datetime',
        'status' => 'integer',
        'rating' => 'integer',
    ];

    // Status Constants
    const STATUS_DRAFT = 0;
    const STATUS_SUBMITTED = 1;
    const STATUS_REVIEWED = 2;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->kode_monev)) {
                // Pakai MAX(id) bukan count() untuk hindari duplikat saat ada delete/race
                $year = date('Y');
                $lastNumber = (int) static::whereYear('created_at', $year)
                    ->whereNotNull('kode_monev')
                    ->where('kode_monev', 'like', "MON/{$year}/%")
                    ->get()
                    ->map(fn($m) => (int) substr($m->kode_monev, -4))
                    ->max();
                $next = max($lastNumber + 1, static::max('id') + 1);
                $model->kode_monev = 'MON/' . $year . '/' . str_pad($next, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // Relationships
    public function permohonan(): BelongsTo
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(Pemohon::class, 'id_pemohon');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * User yang submit monev (siapa pun: pemohon/tkksd_lokus/admin).
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    /**
     * TKKSD Lokus yang menyetujui evaluasi pemohon
     */
    public function tkksdLokus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tkksd_lokus');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Menunggu Review',
            self::STATUS_REVIEWED => 'Sudah Direview',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_SUBMITTED => 'orange',
            self::STATUS_REVIEWED => 'green',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }

    public function scopeByPemohon($query, $pemohonId)
    {
        return $query->where('id_pemohon', $pemohonId);
    }
}
