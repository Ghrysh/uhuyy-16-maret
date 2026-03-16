<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UserDetail extends Model
{
    protected $table = 'user_details';

    protected $primaryKey = 'id';

    public $incrementing = false; // karena UUID
    protected $keyType = 'string'; // UUID = string

    protected $fillable = [
        'nip',
        'nip_baru',
        'nama',
        'nama_lengkap',
        'agama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'pendidikan',
        'jenjang_pendidikan',
        'kode_level_jabatan',
        'level_jabatan',
        'pangkat',
        'gol_ruang',
        'tmt_cpns',
        'tmt_pangkat',
        'mk_tahun',
        'mk_bulan',
        'gaji_pokok',
        'tipe_jabatan',
        'kode_jabatan',
        'tampil_jabatan',
        'tmt_jabatan',
        'kode_satuan_kerja',
        'satker_1',
        'satker_2',
        'kode_satker_2',
        'satker_3',
        'kode_satker_3',
        'satker_4',
        'kode_satker_4',
        'satker_5',
        'kode_satker_5',
        'kode_grup_satuan_kerja',
        'grup_satuan_kerja',
        'keterangan_satuan_kerja',
        'status_kawin',
        'alamat_1',
        'alamat_2',
        'telepon',
        'no_hp',
        'email',
        'kab_kota',
        'provinsi',
        'kode_pos',
        'kode_lokasi',
        'iso',
        'kode_pangkat',
        'keterangan',
        'tmt_pangkat_yad',
        'tmt_kgb_yad',
        'usia_pensiun',
        'tmt_pensiun',
        'mk_tahun_1',
        'mk_bulan_1',
        'nsm',
        'npsn',
        'kode_kua',
        'kode_bidang_studi',
        'bidang_studi',
        'status_pegawai',
        'lat',
        'lon',
        'satker_kelola',
        'hari_kerja',
        'email_dinas',
    ];

    protected $casts = [
        'tanggal_lahir' => 'datetime',
        'tmt_jabatan' => 'datetime',
        'tmt_pangkat_yad' => 'datetime',
        'tmt_kgb_yad' => 'datetime',
        'tmt_pensiun' => 'datetime',
        'tmt_cpns' => 'date',
        'tmt_pangkat' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function user2()
    {
        return $this->belongsTo(User::class, 'nip_baru', 'nip');
    }

    public function satker()
    {
        return $this->hasOneThrough(
            Satker::class,
            User::class,
            'nip',         // key di User → string
            'id',          // key di Satker → UUID
            'nip_baru',    // local key di UserDetail
            'satker_id'    // key di User
        );
    }

}
