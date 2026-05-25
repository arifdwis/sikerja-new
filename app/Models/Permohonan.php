<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permohonan extends Model
{
    protected $table = 'permohonan';

    protected $fillable = [
        'uuid',
        'id_pemohon_0',
        'id_pemohon_1',
        'id_provinsi',
        'id_kota',
        'id_kategori',
        'slug',
        'nama_instansi',
        'nomor_permohonan',
        'kode',
        'alamat',
        'lokasi',
        'kode_pos',
        'email',
        'telepon',
        'website',
        'status',
        'status_selesai',
        'alasan_tolak',
        'label',
        'latar_belakang',
        'maksud_tujuan',
        'lokasi_kerjasama',
        'ruang_lingkup',
        'jangka_waktu',
        'tanggal_mulai',
        'tanggal_berakhir',
        'manfaat',
        'analisis_dampak',
        'pembiayaan',
    ];

    protected $appends = ['status_label'];

    protected $casts = [
        'status'           => 'integer',
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /**
     * Status constants - Business Flow Baru:
     * 0: Permohonan Baru - Menunggu Validasi
     * 1: Pembahasan (setelah admin validasi)
     * 2: Penjadwalan Penandatanganan (setelah pembahasan selesai)
     * 3: Jadwal Disetujui - Pemohon Upload PKS Final
     * 4: Menunggu Penandatanganan (PKS terupload, menunggu hari TTD)
     * 5: Pasca-Tandatangan (Pemohon upload dokumen yang sudah di-TTD)
     * 6: Pelaksanaan Kerjasama (Admin approve PKS final)
     * 7: Selesai (Monev sudah final dibuat admin)
     * 9: Ditolak
     */
    const STATUS_PERMOHONAN           = 0;  // Permohonan Baru - Menunggu Validasi
    const STATUS_PEMBAHASAN           = 1;  // Pembahasan (setelah divalidasi)
    const STATUS_PENJADWALAN          = 2;  // Penjadwalan Penandatanganan
    const STATUS_JADWAL_DISETUJUI     = 3;  // Jadwal Disetujui - Upload PKS
    const STATUS_MENUNGGU_TANDATANGAN = 4;  // PKS diupload - menunggu TTD
    const STATUS_PASCA_TANDATANGAN    = 5;  // Dokumen TTD diupload pemohon
    const STATUS_PELAKSANAAN          = 6;  // Pelaksanaan Kerjasama (aktif, sebelum tanggal_berakhir)
    const STATUS_SELESAI              = 7;  // Selesai (tanggal_berakhir kerjasama sudah lewat)
    const STATUS_DITOLAK              = 9;  // Ditolak

    // Backward compatibility aliases
    const STATUS_DISPOSISI = 1;    // Alias for PEMBAHASAN — masih dipakai di beberapa controller legacy
    // STATUS_DISETUJUI dihapus karena ambigu dengan PermohonanFile::STATUS_DISETUJUI.
    // Gunakan STATUS_PELAKSANAAN (6) untuk kerja sama yang sudah disetujui dan masuk pelaksanaan.

    /**
     * Status labels.
     * - color    : Tailwind background class (untuk card/badge custom)
     * - text     : Tailwind text class
     * - severity : PrimeVue Tag severity (success, info, warning, danger, secondary, contrast)
     * 
     * Warna setiap status sengaja dibuat kontras agar pengguna mudah membedakan
     * tahap workflow di card list permohonan.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PERMOHONAN           => ['label' => 'Menunggu Validasi',                  'color' => 'bg-amber-500',   'text' => 'text-amber-700',   'severity' => 'warning'],
            self::STATUS_PEMBAHASAN           => ['label' => 'Dalam Pembahasan',                   'color' => 'bg-sky-500',     'text' => 'text-sky-700',     'severity' => 'info'],
            self::STATUS_PENJADWALAN          => ['label' => 'Menunggu Jadwal Penandatanganan',    'color' => 'bg-blue-600',    'text' => 'text-blue-700',    'severity' => 'info'],
            self::STATUS_JADWAL_DISETUJUI     => ['label' => 'Upload PKS Final',                   'color' => 'bg-violet-600',  'text' => 'text-violet-700',  'severity' => 'contrast'],
            self::STATUS_MENUNGGU_TANDATANGAN => ['label' => 'Menunggu Penandatanganan',           'color' => 'bg-pink-500',    'text' => 'text-pink-700',    'severity' => 'contrast'],
            self::STATUS_PASCA_TANDATANGAN    => ['label' => 'Validasi Dokumen Pasca-Tandatangan', 'color' => 'bg-orange-500',  'text' => 'text-orange-700',  'severity' => 'contrast'],
            self::STATUS_PELAKSANAAN          => ['label' => 'Pelaksanaan Kerjasama',              'color' => 'bg-teal-500',    'text' => 'text-teal-700',    'severity' => 'success'],
            self::STATUS_SELESAI              => ['label' => 'Selesai',                           'color' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'severity' => 'success'],
            self::STATUS_DITOLAK              => ['label' => 'Ditolak',                           'color' => 'bg-red-600',     'text' => 'text-red-700',     'severity' => 'danger'],
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->slug = Str::slug($model->label);
        });
    }

    /**
     * Scope for UUID
     */
    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
     * Scope for SLUG
     */
    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute(): array
    {
        return self::statusLabels()[$this->status] ?? ['label' => 'Unknown', 'color' => 'bg-gray-500', 'text' => 'text-gray-700'];
    }

    /**
     * Relationships
     */
    public function pemohon1()
    {
        return $this->belongsTo(Pemohon::class, 'id_pemohon_0', 'id_operator');
    }

    public function pemohon2()
    {
        return $this->belongsTo(Pemohon::class, 'id_pemohon_1');
    }

    /**
     * Alias for pemohon1 - used in new controllers
     */
    public function pemohon()
    {
        return $this->belongsTo(Pemohon::class, 'id_pemohon_0', 'id_operator');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'id_pemohon_0');
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class, 'id_kota');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function files()
    {
        return $this->hasMany(PermohonanFile::class, 'id_permohonan');
    }

    public function historis()
    {
        return $this->hasMany(PermohonanHistori::class, 'id_permohonan');
    }

    public function penjadwalans()
    {
        return $this->hasMany(Penjadwalan::class, 'id_permohonan');
    }

    public function latestHistori()
    {
        return $this->hasOne(PermohonanHistori::class, 'id_permohonan')->latestOfMany();
    }

    public function monev()
    {
        // Pemohon biasanya hanya punya satu monev — relasi ini menampilkan monev terbaru
        // (ada di banyak query existing yang pakai whereHas/whereDoesntHave)
        return $this->hasOne(Monev::class, 'id_permohonan')->latestOfMany();
    }

    public function monevs()
    {
        return $this->hasMany(Monev::class, 'id_permohonan');
    }

    /**
     * OPD yang terlibat dalam permohonan (many-to-many)
     */
    public function opds()
    {
        return $this->belongsToMany(Opd::class, 'permohonan_opd', 'id_permohonan', 'id_opd');
    }

    /**
     * File PKS final yang diupload
     */
    public function pksFiles()
    {
        return $this->hasMany(PermohonanPks::class, 'id_permohonan');
    }

    /**
     * File dokumen pasca-tandatangan
     */
    public function ttdFiles()
    {
        return $this->hasMany(PermohonanTtd::class, 'id_permohonan');
    }
}
