<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jabatan extends Model
{
    use HasUuids;

    protected $table = 'jabatan';
    protected $fillable = ['kode_jabatan', 'nama_jabatan', 'jenis_jabatan_id', 'jenis_satker_id', 'jabatan_fungsional_id'];

    public function fungsional(): BelongsTo
    {
        return $this->belongsTo(JabatanFungsional::class, 'jabatan_fungsional_id');
    }

    public function jenis()
    {
        return $this->belongsTo(MJenisJabatan::class, 'jenis_jabatan_id');
    }

    // relasi ke m_jenis_satker
    public function jenisSatker()
    {
        return $this->belongsTo(MJenisSatker::class, 'jenis_satker_id');
    }
}