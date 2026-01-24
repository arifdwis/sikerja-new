<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table = 'master_provinces';

    protected $fillable = [
        'name',
    ];

    public function kotas()
    {
        return $this->hasMany(Kota::class, 'province_id');
    }
}
