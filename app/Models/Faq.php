<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Faq extends Model
{
    protected $table = 'faq';

    protected $fillable = [
        'uuid',
        'label',
        'jawaban',
        'slug',
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
