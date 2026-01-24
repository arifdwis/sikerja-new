<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Notifikasi extends Model
{
    use SoftDeletes;

    protected $table = 'notifikasi';

    protected $fillable = [
        'uuid',
        'id_user',
        'id_permohonan',
        'from_user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
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

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function permohonan()
    {
        return $this->belongsTo(Permohonan::class, 'id_permohonan');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
