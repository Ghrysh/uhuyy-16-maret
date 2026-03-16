<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasUuids, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'satker_id', 'nip'];
    protected $hidden = ['password', 'remember_token'];

    // Menangani UUID karena tidak auto-increment
    public $incrementing = false;
    protected $keyType = 'string';

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function roles()
    {
        return $this->belongsToMany(MRole::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Helper untuk cek role
     */
    public function hasRole($roleKey)
    {
        return $this->roles()->where('key', $roleKey)->exists();
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class, 'user_id');
    }

    /**
     * Penugasan aktif (status_aktif = true)
     */
    public function penugasanAktif(): HasOne
    {
        return $this->hasOne(Penugasan::class, 'user_id')
            ->where('status_aktif', true);
    }

    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'nip_baru', 'nip');
    }


    public function penugasan()
    {
        return $this->hasMany(Penugasan::class, 'user_id');
    }

    public function satker()
    {
        return $this->belongsTo(Satker::class, 'satker_id');
    }


}