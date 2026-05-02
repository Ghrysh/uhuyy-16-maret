<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RumusKode extends Model
{
    protected $fillable = [
        'nama_rumus',
        'pola',
        'keterangan',
        'is_applied',
        'is_auto_name',
        'base_auto_name',
        'is_name_locked',
        'custom_names_map',
        'jenis_satker_id',
        'tingkat_wilayah_id',
        'ref_jabatan_satker_id'
    ];

    protected $casts = [
        'is_applied' => 'boolean',
        'is_auto_name' => 'boolean',
        'is_name_locked' => 'boolean',
        'custom_names_map' => 'array',
    ];
}