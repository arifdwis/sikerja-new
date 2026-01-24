<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pemohon extends Model
{
    protected $table = 'pemohon';

    protected $fillable = [
        'id_operator',
        'uuid',
        'name',
        'foto',
        'jabatan',
        'unit_kerja',
        'nip',
        'nik',
        'nik_file',
        'slug',
        'email',
        'phone',
        'nickname',
        'address',
        'kota',
        'kota_id',
        'gender',
        'date_birth',
        // Corporate/Instansi fields
        'nama_instansi',
        'alamat_instansi',
        'telepon_instansi',
        'email_instansi',
        'website',
        'bidang_usaha',
        'nama_pimpinan',
        'jabatan_pimpinan',
        'instansi', // legacy field alias
    ];

    protected $casts = [
        'date_birth' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->slug = Str::slug($model->name);
        });
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public function scopeSlug($query, $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Get photo URL attribute
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    protected $appends = ['foto_url'];

    /**
     * Relationships
     */
    public function operator()
    {
        return $this->belongsTo(User::class, 'id_operator');
    }

    public function kotaRef()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'id_pemohon_1');
    }
}
