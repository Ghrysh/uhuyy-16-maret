<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DistribusiKuota extends Model
{
    use HasUuids;

    protected $fillable = [
        'satker_id', 
        'jabatan_id',
        'kuota_pertama', 
        'kuota_muda', 
        'kuota_madya', 
        'kuota_utama'
    ];
}