<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MitraKerjasama extends Model
{
    protected $table = 'mitra_kerjasama';

    protected $fillable = [
        'uuid',
        'kategori_mitra_id',
        'nama',
        'logo',
        'url_website',
        'deskripsi',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'kategori_mitra_id' => 'integer',
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

    public function kategori()
    {
        return $this->belongsTo(KategoriMitra::class, 'kategori_mitra_id');
    }
}
