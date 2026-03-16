<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penugasan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'penugasan';

    protected $fillable = [
        'user_id',
        'satker_id',
        'jabatan_id',
        'jenis_penugasan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status_aktif'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'satker_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function jenisPenugasan(): BelongsTo
    {
        // Sesuaikan 'MJenisPenugasan' dengan nama class model yang Anda miliki
        return $this->belongsTo(MJenisPenugasan::class, 'jenis_penugasan_id');
    }
}