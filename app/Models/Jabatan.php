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
    protected $fillable = [
        'nama_jabatan',
        'kode_jabatan',
        'jenis_jabatan_id',
        'jabatan_fungsional_id',
        'periode_id',
        'baseline',
        
        // Baseline MENPANRB
        'b_pertama_menpan',
        'b_muda_menpan',
        'b_madya_menpan',
        'b_utama_menpan',
    
        // Baseline EKSISTING
        'b_pertama_eksisting',
        'b_muda_eksisting',
        'b_madya_eksisting',
        'b_utama_eksisting',

        // Baseline LOWONGAN
        'b_pertama_lowongan',
        'b_muda_lowongan',
        'b_madya_lowongan',
        'b_utama_lowongan',

        'b_lima_menpan', 'b_enam_menpan', 'b_tujuh_menpan', 'b_delapan_menpan',
        'b_lima_eksisting', 'b_enam_eksisting', 'b_tujuh_eksisting', 'b_delapan_eksisting',
        'b_lima_lowongan', 'b_enam_lowongan', 'b_tujuh_lowongan', 'b_delapan_lowongan',
    ];

    public function periode()
    {
        return $this->belongsTo(\App\Models\Periode::class, 'periode_id');
    }

    public function fungsional(): BelongsTo
    {
        return $this->belongsTo(JabatanFungsional::class, 'jabatan_fungsional_id');
    }

    public function jenis()
    {
        return $this->belongsTo(MJenisJabatan::class, 'jenis_jabatan_id');
    }

    public function jenisSatker()
    {
        return $this->belongsTo(MJenisSatker::class, 'jenis_satker_id');
    }
}