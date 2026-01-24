<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PermohonanFile extends Model
{
    use SoftDeletes;

    protected $table = 'permohonan_file';

    protected $fillable = [
        'uuid',
        'id_permohonan',
        'label',
        'file',
        'file_path',
        'file_name',
        'deskripsi',
        'slug',
        'status',
        'komentar',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_DIPROSES = 0;
    const STATUS_DISETUJUI = 1;
    const STATUS_DITOLAK = 2;

    /**
     * Get status labels
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_DIPROSES => ['label' => 'Menunggu Review', 'color' => 'warning'],
            self::STATUS_DISETUJUI => ['label' => 'Disetujui', 'color' => 'success'],
            self::STATUS_DITOLAK => ['label' => 'Ditolak', 'color' => 'danger'],
        ];
    }

    /**
     * Get status label attribute
     */
    public function getStatusLabelAttribute(): array
    {
        return self::statusLabels()[$this->status] ?? ['label' => 'Unknown', 'color' => 'secondary'];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
            $model->slug = Str::slug($model->label);
        });
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    /**
     * Get file URL attribute
     */
    public function getFileUrlAttribute(): ?string
    {
        return $this->file ? asset($this->file) : null;
    }

    protected $appends = ['file_url', 'status_label'];

    /**
     * Relationships
     */
    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function historis()
    {
        return $this->hasMany(PermohonanHistori::class, 'id_file');
    }
}
