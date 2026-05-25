<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PermohonanTtd extends Model
{
    protected $table = 'permohonan_ttd';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'file_path',
        'file_name',
        'tipe',                // 'pemohon' | 'admin'
        'checklist_paraf',
        'checklist_materai',
        'checklist_stempel',
        'is_validated',
        'validated_by',
        'validated_at',
        'catatan_admin',
        'uploaded_by',
    ];

    protected $casts = [
        'checklist_paraf'   => 'boolean',
        'checklist_materai' => 'boolean',
        'checklist_stempel' => 'boolean',
        'is_validated'      => 'boolean',
        'validated_at'      => 'datetime',
    ];

    const TIPE_PEMOHON = 'pemohon';
    const TIPE_ADMIN   = 'admin';

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

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    /**
     * Cek semua checklist sudah dicentang
     */
    public function isChecklistComplete(): bool
    {
        return $this->checklist_paraf && $this->checklist_materai && $this->checklist_stempel;
    }

    protected $appends = ['file_url'];
}
