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
        'nama_pimpinan',
        'jabatan_pimpinan',
        'nip_pimpinan',
        'pic1_nama',
        'pic1_jabatan',
        'pic1_phone',
        'pic1_email',
        'pic2_nama',
        'pic2_jabatan',
        'pic2_phone',
        'pic2_email',
        'tipe_dokumen',
        'id_kb_induk',
        'is_draft',
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
        'is_draft'         => 'boolean',
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    /**
     * Status constants - Alur Proses Bisnis SIKERJA:
     * -1: Draft Pemohon (belum diajukan)
     *  0: Diajukan Pemohon - Menunggu Verifikasi Administrasi
     * 10: Lolos Administrasi - Menunggu Validasi/Disposisi Kabag
     * 11: Dikembalikan untuk Revisi (oleh Kabag/Admin)
     * 12: Terverifikasi Kabag - Siap Masuk Pembahasan Draft KB
     *  1: Pembahasan Draft Kesepakatan Bersama (TKKSD)
     * 13: Finalisasi Draft KB (Draft KB Final diunggah Admin)
     *  2: Pengajuan Jadwal Penandatanganan KB
     *  3: Jadwal KB Disetujui
     *  4: Menunggu Penandatanganan KB
     *  5: Dokumen KB Sah Bertanda Tangan (KB Aktif)
     * 
     * Siklus PKS (Perjanjian Kerja Sama Turunan):
     * 20: Pengajuan Draft PKS oleh Pemohon / OPD
     * 21: Pembahasan Teknis PKS (OPD & Mitra)
     * 22: Finalisasi Draft PKS (PKS Final)
     * 23: Penandatanganan PKS oleh Kepala OPD & Mitra
     * 
     * Pelaksanaan, Monitoring, Evaluasi:
     *  6: Pelaksanaan Kerjasama (PKS Sah, kerjasama aktif)
     * 25: Evaluasi Kerjasama (H-90 Hari sebelum berakhir)
     * 26: Usulan Perpanjangan Kerjasama
     *  7: Selesai (Kerjasama berakhir)
     *  8: Dicabut (karena pelanggaran pelaksanaan)
     *  9: Ditolak (penolakan formal)
     */
    const STATUS_DRAFT                = -1; // Draft Pemohon
    const STATUS_PERMOHONAN           = 0;  // Diajukan Pemohon - Menunggu Verifikasi
    const STATUS_VERIFIKASI_ADMIN     = 10; // Lolos Administrasi - Menunggu Validasi Kabag
    const STATUS_REVISI               = 11; // Dikembalikan untuk Revisi
    const STATUS_TERVERIFIKASI        = 12; // Terverifikasi Kabag
    const STATUS_PEMBAHASAN           = 1;  // Pembahasan Draft KB (TKKSD)
    const STATUS_KB_FINAL             = 13; // Draft KB Final diunggah Admin
    const STATUS_PENJADWALAN          = 2;  // Penjadwalan Penandatanganan KB
    const STATUS_JADWAL_DISETUJUI     = 3;  // Jadwal Disetujui
    const STATUS_MENUNGGU_TANDATANGAN = 4;  // Menunggu Penandatanganan KB
    const STATUS_PASCA_TANDATANGAN    = 5;  // Dokumen KB Bertanda Tangan (KB Aktif)
    
    // Siklus PKS
    const STATUS_PENGAJUAN_PKS        = 20; // Pengajuan Draft PKS
    const STATUS_PEMBAHASAN_PKS       = 21; // Pembahasan Teknis PKS
    const STATUS_PKS_FINAL            = 22; // Draft PKS Final
    const STATUS_TTD_PKS              = 23; // Penandatanganan PKS
    
    // Pelaksanaan & Monev
    const STATUS_PELAKSANAAN          = 6;  // Pelaksanaan Kerjasama (PKS Aktif)
    const STATUS_EVALUASI             = 25; // Masa Evaluasi (H-90 Hari)
    const STATUS_PERPANJANGAN         = 26; // Usulan Perpanjangan
    const STATUS_SELESAI              = 7;  // Selesai
    const STATUS_DICABUT              = 8;  // Dicabut
    const STATUS_DITOLAK              = 9;  // Ditolak

    // Backward compatibility aliases
    const STATUS_DISPOSISI = 1;

    /**
     * Status labels.
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT                => ['label' => 'Draft Permohonan',                   'color' => 'bg-slate-500',   'text' => 'text-slate-700',   'severity' => 'secondary'],
            self::STATUS_PERMOHONAN           => ['label' => 'Menunggu Verifikasi Administrasi',   'color' => 'bg-amber-500',   'text' => 'text-amber-700',   'severity' => 'warning'],
            self::STATUS_VERIFIKASI_ADMIN     => ['label' => 'Menunggu Validasi Kabag',           'color' => 'bg-indigo-500',  'text' => 'text-indigo-700',  'severity' => 'info'],
            self::STATUS_REVISI               => ['label' => 'Perlu Perbaikan / Revisi',          'color' => 'bg-orange-500',  'text' => 'text-orange-700',  'severity' => 'warning'],
            self::STATUS_TERVERIFIKASI        => ['label' => 'Terverifikasi (Siap Bahas KB)',      'color' => 'bg-cyan-500',    'text' => 'text-cyan-700',    'severity' => 'info'],
            self::STATUS_PEMBAHASAN           => ['label' => 'Pembahasan Draft KB',                'color' => 'bg-sky-500',     'text' => 'text-sky-700',     'severity' => 'info'],
            self::STATUS_KB_FINAL             => ['label' => 'Draft KB Final Disetujui',           'color' => 'bg-purple-600',  'text' => 'text-purple-700',  'severity' => 'contrast'],
            self::STATUS_PENJADWALAN          => ['label' => 'Pengajuan Jadwal TTD KB',            'color' => 'bg-blue-600',    'text' => 'text-blue-700',    'severity' => 'info'],
            self::STATUS_JADWAL_DISETUJUI     => ['label' => 'Jadwal TTD KB Disetujui',            'color' => 'bg-violet-600',  'text' => 'text-violet-700',  'severity' => 'contrast'],
            self::STATUS_MENUNGGU_TANDATANGAN => ['label' => 'Menunggu Penandatanganan KB',        'color' => 'bg-pink-500',    'text' => 'text-pink-700',    'severity' => 'contrast'],
            self::STATUS_PASCA_TANDATANGAN    => ['label' => 'Dokumen KB Sah (KB Aktif)',          'color' => 'bg-teal-600',    'text' => 'text-teal-700',    'severity' => 'success'],
            
            // Siklus PKS
            self::STATUS_PENGAJUAN_PKS        => ['label' => 'Pengajuan Draf PKS Turunan',         'color' => 'bg-amber-600',   'text' => 'text-amber-700',   'severity' => 'warning'],
            self::STATUS_PEMBAHASAN_PKS       => ['label' => 'Pembahasan Teknis PKS',              'color' => 'bg-cyan-600',    'text' => 'text-cyan-700',    'severity' => 'info'],
            self::STATUS_PKS_FINAL            => ['label' => 'Draf PKS Final',                     'color' => 'bg-indigo-600',  'text' => 'text-indigo-700',  'severity' => 'contrast'],
            self::STATUS_TTD_PKS              => ['label' => 'Penandatanganan PKS OPD',            'color' => 'bg-purple-600',  'text' => 'text-purple-700',  'severity' => 'contrast'],
            
            // Pelaksanaan & Evaluasi
            self::STATUS_PELAKSANAAN          => ['label' => 'Pelaksanaan Kerjasama (PKS Aktif)',  'color' => 'bg-teal-500',    'text' => 'text-teal-700',    'severity' => 'success'],
            self::STATUS_EVALUASI             => ['label' => 'Evaluasi Akhir (H-90 Hari)',         'color' => 'bg-yellow-600',  'text' => 'text-yellow-700',  'severity' => 'warning'],
            self::STATUS_PERPANJANGAN         => ['label' => 'Usulan Perpanjangan',                'color' => 'bg-blue-500',    'text' => 'text-blue-700',    'severity' => 'info'],
            self::STATUS_SELESAI              => ['label' => 'Selesai',                           'color' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'severity' => 'success'],
            self::STATUS_DICABUT              => ['label' => 'Dicabut',                           'color' => 'bg-rose-700',    'text' => 'text-rose-700',    'severity' => 'danger'],
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

    /**
     * Relasi ke Kesepakatan Bersama (KB) Induk jika permohonan ini bertipe PKS Turunan
     */
    public function kbInduk()
    {
        return $this->belongsTo(Permohonan::class, 'id_kb_induk');
    }

    /**
     * Relasi ke daftar PKS turunan yang dibuat dari KB ini
     */
    public function pksTurunan()
    {
        return $this->hasMany(Permohonan::class, 'id_kb_induk');
    }
}
