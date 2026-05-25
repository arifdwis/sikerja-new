<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class KerjasamaManual extends Model
{
    use SoftDeletes;

    protected $table = 'kerjasama_manual';

    protected $fillable = [
        'uuid',
        'nomor_pks',
        'nama_instansi',
        'label',
        'id_kategori',
        'ruang_lingkup',
        'tanggal_mulai',
        'tanggal_berakhir',
        'jangka_waktu',
        'file_pks',
        'file_pks_name',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function opds()
    {
        return $this->belongsToMany(Opd::class, 'kerjasama_manual_opd', 'id_kerjasama_manual', 'id_opd');
    }

    public function getFilePksUrlAttribute(): ?string
    {
        return $this->file_pks ? asset('storage/' . $this->file_pks) : null;
    }

    protected $appends = ['file_pks_url'];
}
