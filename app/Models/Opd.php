<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Opd extends Model
{
    protected $table = 'opd';

    protected $fillable = [
        'nama',
        'singkatan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Permohonan yang melibatkan OPD ini (many-to-many)
     */
    public function permohonans()
    {
        return $this->belongsToMany(Permohonan::class, 'permohonan_opd', 'id_opd', 'id_permohonan');
    }

    /**
     * Kerjasama manual yang melibatkan OPD ini
     */
    public function kerjasamaManuals()
    {
        return $this->belongsToMany(KerjasamaManual::class, 'kerjasama_manual_opd', 'id_opd', 'id_kerjasama_manual');
    }

    /**
     * User yang terhubung ke OPD ini (TKKSD Lokus / pemohon internal)
     */
    public function users()
    {
        return $this->hasMany(User::class, 'id_opd');
    }

    /**
     * Scope: hanya OPD aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
