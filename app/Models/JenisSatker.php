<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSatker extends Model
{
    // Karena nama tabel tidak jamak (m_jenis_satker), definisikan manual
    protected $table = 'm_jenis_satker';

    // Kolom yang dapat diisi berdasarkan gambar (id, nama)
    protected $fillable = ['nama'];

    /**
     * Relasi ke tabel Satker
     */
    public function satkers(): HasMany
    {
        return $this->hasMany(Satker::class, 'jenis_satker_id');
    }
}