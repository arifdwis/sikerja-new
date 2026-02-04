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
        // Status
        'status',
        'catatan_admin',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'tanggal_evaluasi' => 'date',
        'reviewed_at' => 'datetime',
        'status' => 'integer',
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
                $model->kode_monev = 'MON/' . date('Y') . '/' . str_pad(static::count() + 1, 4, '0', STR_PAD_LEFT);
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
