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

    /**
     * Status constants - Business Flow:
     * 0: Permohonan Baru
     * 1: Pembahasan (setelah admin validasi)
     * 2: Penjadwalan (pemohon buat jadwal meeting)
     * 3: Disetujui (admin approve jadwal) → langsung selesai
     * 4: Selesai
     * 9: Ditolak
     */
    const STATUS_PERMOHONAN = 0;   // Permohonan Baru - Menunggu Validasi
    const STATUS_PEMBAHASAN = 1;   // Pembahasan (setelah divalidasi)
    const STATUS_PENJADWALAN = 2;  // Pemohon buat jadwal meeting
    const STATUS_DISETUJUI = 3;    // Admin approve jadwal
    const STATUS_SELESAI = 4;      // Kerjasama Selesai
    const STATUS_DITOLAK = 9;      // Ditolak

    // Backward compatibility aliases
    const STATUS_DISPOSISI = 1;    // Alias for PEMBAHASAN

    /**
     * Status labels
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_PERMOHONAN => ['label' => 'Menunggu Validasi', 'color' => 'bg-teal-600', 'text' => 'text-teal-700'],
            self::STATUS_PEMBAHASAN => ['label' => 'Dalam Pembahasan', 'color' => 'bg-cyan-600', 'text' => 'text-cyan-700'],
            self::STATUS_PENJADWALAN => ['label' => 'Menunggu Penjadwalan', 'color' => 'bg-blue-600', 'text' => 'text-blue-700'],
            self::STATUS_DISETUJUI => ['label' => 'Disetujui', 'color' => 'bg-indigo-600', 'text' => 'text-indigo-700'],
            self::STATUS_SELESAI => ['label' => 'Selesai', 'color' => 'bg-green-600', 'text' => 'text-green-700'],
            self::STATUS_DITOLAK => ['label' => 'Ditolak', 'color' => 'bg-red-600', 'text' => 'text-red-700'],
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
}
