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
        
        // Kuota MENPANRB
        'kp_menpan',
        'kmu_menpan',
        'kma_menpan',
        'ku_menpan',

        // Kuota EKSISTING
        'kp_eksisting',
        'kmu_eksisting',
        'kma_eksisting',
        'ku_eksisting',

        // Kuota LOWONGAN
        'kp_lowongan',
        'kmu_lowongan',
        'kma_lowongan',
        'ku_lowongan',

        // Kuota Jenjang 5-8
        'k5_menpan', 'k6_menpan', 'k7_menpan', 'k8_menpan',
        'k5_eksisting', 'k6_eksisting', 'k7_eksisting', 'k8_eksisting',
        'k5_lowongan', 'k6_lowongan', 'k7_lowongan', 'k8_lowongan',
    ];
}