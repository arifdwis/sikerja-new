<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PermohonanPks extends Model
{
    protected $table = 'permohonan_pks';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'file_path',
        'file_name',
        'tipe',          // 'pemohon' | 'admin'
        'status',        // 'pending' | 'approved' | 'rejected'
        'uploaded_by',
        'catatan',
    ];

    const TIPE_PEMOHON = 'pemohon';
    const TIPE_ADMIN   = 'admin';

    const STATUS_PENDING  = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    protected $appends = ['file_url'];
}
