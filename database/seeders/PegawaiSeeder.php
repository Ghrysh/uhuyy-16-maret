<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PegawaiSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pegawai')->insert([
            [
                'id' => Str::uuid(),
                'nip' => '198001012006041001',
                'nama_lengkap' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'nip' => '198203152007011002',
                'nama_lengkap' => 'Siti Nurhaliza',
                'email' => 'siti.nurhaliza@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'nip' => '198507102008031003',
                'nama_lengkap' => 'Budi Santoso',
                'email' => 'budi.santoso@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'nip' => '198611212009041004',
                'nama_lengkap' => 'Dewi Anggraini',
                'email' => 'dewi.anggraini@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'nip' => '198904302010021005',
                'nama_lengkap' => 'Rizky Pratama',
                'email' => 'rizky.pratama@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid(),
                'nip' => '199102182011031006',
                'nama_lengkap' => 'Lina Marlina',
                'email' => 'lina.marlina@example.com',
                'wilayah_id' => null,
                'satker_id' => null,
                'jabatan_aktif_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
