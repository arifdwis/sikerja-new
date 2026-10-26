<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GaleriTkksd extends Model
{
    protected $table = 'galeri_tkksd';

    protected $fillable = [
        'uuid',
        'judul',
        'deskripsi',
        'foto',
        'tanggal_kegiatan',
        'kategori_kegiatan',
        'lokasi',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'tanggal_kegiatan' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
}
