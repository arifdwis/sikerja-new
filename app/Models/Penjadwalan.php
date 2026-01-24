<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Penjadwalan extends Model
{
    protected $table = 'penjadwalan';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'id_histori',
        'tipe', // calendar, langsung
        'tanggal',
        'waktu',
        'lokasi',
        'agenda',
        'keterangan',
        'status', // 0: Menunggu, 1: Disetujui, 2: Ditolak/Reschedule
        'approved_by',
        'approved_at',
        'admin_comment',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu' => 'datetime',
        'approved_at' => 'datetime',
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

    public function histori()
    {
        return $this->belongsTo(PermohonanHistori::class, 'id_histori');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
