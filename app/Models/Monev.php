<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Monev extends Model
{
    protected $table = 'monevs';

    /**
     * Tabel `monevs` sekarang ringan (identitas + role + status + tanggal).
     * Jawaban detail dipindah ke 3 tabel terpisah:
     *   - monev_admin_details   (admin: 11 jawaban + rating + file_bukti)
     *   - monev_pemohon_details (pmh_*)
     *   - monev_tkksd_details   (tkl_*)
     */
    protected $fillable = [
        'uuid',
        'id_permohonan',
        'id_pemohon',
        'kode_monev',
        'tanggal_evaluasi',
        // Tipe: reguler / manual (Req 16)
        'tipe',
        // Submitter role + user (pemohon/admin/tkksd_lokus monev mandiri-paralel)
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
    ];

    /**
     * Daftar atribut yang dulu kolom langsung di `monevs` dan sekarang
     * dipindah ke detail tables. Diakses lewat magic accessor di __get().
     */
    public const ADMIN_DETAIL_KEYS = [
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

    public const PEMOHON_DETAIL_KEYS = [
        'pmh_realisasi_kegiatan',
        'pmh_kesesuaian_output',
        'pmh_pemanfaatan_hasil',
        'pmh_kendala_lapangan',
        'pmh_keberlanjutan',
        'pmh_file_laporan',
    ];

    public const TKKSD_DETAIL_KEYS = [
        'tkl_kepatuhan_pks',
        'tkl_koordinasi_mitra',
        'tkl_kesesuaian_anggaran',
        'tkl_temuan_pengawasan',
        'tkl_rekomendasi_lokus',
        'tkl_catatan',
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

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

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

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitter_user_id');
    }

    public function tkksdLokus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_tkksd_lokus');
    }

    public function adminDetail(): HasOne
    {
        return $this->hasOne(MonevAdminDetail::class, 'monev_id');
    }

    public function pemohonDetail(): HasOne
    {
        return $this->hasOne(MonevPemohonDetail::class, 'monev_id');
    }

    public function tkksdDetail(): HasOne
    {
        return $this->hasOne(MonevTkksdDetail::class, 'monev_id');
    }

    /**
     * Helper: ambil objek detail sesuai submitter_role.
     */
    public function detail(): ?Model
    {
        return match ($this->submitter_role) {
            'admin' => $this->adminDetail,
            'pemohon' => $this->pemohonDetail,
            'tkksd_lokus' => $this->tkksdDetail,
            default => null,
        };
    }

    // ------------------------------------------------------------------
    // Magic accessor: jaga back-compat untuk akses $monev->kesesuaian_tujuan dll.
    // ------------------------------------------------------------------
    public function __get($key)
    {
        if (in_array($key, self::ADMIN_DETAIL_KEYS, true)) {
            return $this->adminDetail?->{$key};
        }
        if (in_array($key, self::PEMOHON_DETAIL_KEYS, true)) {
            return $this->pemohonDetail?->{$key};
        }
        if (in_array($key, self::TKKSD_DETAIL_KEYS, true)) {
            return $this->tkksdDetail?->{$key};
        }
        return parent::__get($key);
    }

    /**
     * Flatten field detail ke root array supaya JSON response & Inertia props
     * tetap punya bentuk yang sama seperti sebelum split (frontend tak perlu diubah).
     */
    public function toArray()
    {
        $data = parent::toArray();

        $admin = $this->relationLoaded('adminDetail') ? $this->adminDetail : null;
        $pemohon = $this->relationLoaded('pemohonDetail') ? $this->pemohonDetail : null;
        $tkksd = $this->relationLoaded('tkksdDetail') ? $this->tkksdDetail : null;

        foreach (self::ADMIN_DETAIL_KEYS as $k) {
            $data[$k] = $admin?->{$k};
        }
        foreach (self::PEMOHON_DETAIL_KEYS as $k) {
            $data[$k] = $pemohon?->{$k};
        }
        foreach (self::TKKSD_DETAIL_KEYS as $k) {
            $data[$k] = $tkksd?->{$k};
        }

        // Buang relasi mentah supaya payload tidak ganda
        unset($data['admin_detail'], $data['pemohon_detail'], $data['tkksd_detail']);

        return $data;
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------
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
