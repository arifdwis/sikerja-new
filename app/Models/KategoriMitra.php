<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriMitra extends Model
{
    protected $table = 'kategori_mitra';

    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function mitras()
    {
        return $this->hasMany(MitraKerjasama::class, 'kategori_mitra_id')->orderBy('order', 'asc');
    }

    public function activeMitras()
    {
        return $this->hasMany(MitraKerjasama::class, 'kategori_mitra_id')
            ->where('is_active', true)
            ->orderBy('order', 'asc');
    }
}
