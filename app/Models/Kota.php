<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kota extends Model
{
    protected $table = 'master_cities';

    protected $fillable = [
        'province_id',
        'name',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'province_id');
    }

    public function permohonans()
    {
        return $this->hasMany(Permohonan::class, 'id_kota');
    }
}
