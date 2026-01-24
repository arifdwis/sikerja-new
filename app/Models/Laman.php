<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Laman extends Model
{
    protected $table = 'laman';

    protected $fillable = [
        'uuid',
        'label',
        'slug',
        'content',
        'status', // integer (1=active?)
        'id_operator',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->slug = Str::slug($model->label);
            if (!$model->id_operator) {
                $model->id_operator = auth()->id() ?? 0;
            }
        });

        static::updating(function ($model) {
            $model->slug = Str::slug($model->label);
        });
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }
}
