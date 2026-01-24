<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PermohonanHistori extends Model
{
    protected $table = 'permohonan_histori';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'id_operator',
        'id_file',
        'deskripsi',
        'deskripsi_perbaikan',
        'komentar',
        'file',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
     * Relationships
     */
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'id_operator');
    }

    public function fileRef()
    {
        return $this->belongsTo(PermohonanFile::class, 'id_file');
    }

    public function pembahasans()
    {
        return $this->hasMany(PermohonanPembahasan::class, 'id_histori');
    }
}
