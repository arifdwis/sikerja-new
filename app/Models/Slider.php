<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slider extends Model
{
    protected $table = 'slider';

    protected $fillable = [
        'uuid',
        'label',
        'file', // image
        'slug',
        'kategori',
        'id_operator',
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
}
