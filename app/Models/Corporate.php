<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Corporate extends Model
{
    protected $table = 'pemohon_corporate';

    protected $fillable = [
        'uuid',
        'id_operator',
        'name',
        'kota',
        'kota_id',
        'address',
        'postal_code',
        'slug',
        'email',
        'phone',
        'website',
        'status',
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

    public function operator()
    {
        return $this->belongsTo(User::class, 'id_operator');
    }

    public function kotaRef()
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }
}
