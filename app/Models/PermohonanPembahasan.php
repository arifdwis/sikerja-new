<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PermohonanPembahasan extends Model
{
    use SoftDeletes;

    protected $table = 'permohonan_histori_pembahasan';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'id_operator',
        'id_file',
        'id_histori',
        'komentar',
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

    /**
     * Relationships
     */
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'id_operator');
    }

    public function histori()
    {
        return $this->belongsTo(PermohonanHistori::class, 'id_histori');
    }

    public function file()
    {
        return $this->belongsTo(PermohonanFile::class, 'id_file');
    }
}
