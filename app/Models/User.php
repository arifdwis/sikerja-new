<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uid',
        'photo',
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'last_login',
        'last_ip_address',
        'nickname',
        'address',
        'jenis',
        'unit_id',
        'postal_code',
        'kelurahan_id',
        'level',
        'gender',
        'date_birth',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'date_birth' => 'date',
            'password' => 'hashed',
        ];
    }

    /**
     * Get photo URL attribute
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = ['photo_url'];

    /**
     * Get roles for the user (using existing role_users table).
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_users', 'user_id', 'role_id');
    }

    /**
     * Get the pemohon record associated with the user.
     */
    public function pemohon()
    {
        return $this->hasOne(Pemohon::class, 'id_operator');
    }

    /**
     * Get the corporate record associated with the user.
     */
    public function corporate()
    {
        return $this->hasOne(Corporate::class, 'id_operator');
    }

    /**
     * Get notifications for the user.
     */
    public function notifikasis()
    {
        return $this->hasMany(Notifikasi::class, 'id_user');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('slug', $role)->exists();
        }

        if (is_array($role)) {
            return $this->roles()->whereIn('slug', $role)->exists();
        }

        return false;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(['administrator', 'superadmin']);
    }

    /**
     * Check if user is TKKSD
     */
    public function isTkksd(): bool
    {
        return $this->hasRole('tkksd');
    }

    /**
     * Check if user is Pemohon
     */
    public function isPemohon(): bool
    {
        return $this->hasRole('pemohon');
    }

    /**
     * Get primary role name
     */
    public function getRoleNameAttribute(): string
    {
        $role = $this->roles()->first();
        return $role ? $role->name : 'Guest';
    }

    /**
     * Assign a role to user
     */
    public function assignRole($roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role && !$this->roles()->where('role_id', $role->id)->exists()) {
            $this->roles()->attach($role->id);
        }
    }
    /**
     * Check if user has a permission
     */
    public function hasPermission($permission): bool
    {
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }
}
