<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LogSistem extends Model
{
    use HasUuids;

    protected $table = 'log_sistem';

    protected $fillable = [
        'waktu',
        'aksi',
        'nama_tabel',
        'data_id',
        'perubahan',
        'user_id',
    ];

    protected $casts = [
        'waktu' => 'datetime',
    ];

    /**
     * Relasi ke user (pelaku)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
