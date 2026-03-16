<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. m_tingkat_wilayah (ID: Integer)
        DB::table('m_tingkat_wilayah')->insert([
            ['nama' => 'Pusat'],
            ['nama' => 'Provinsi'],
            ['nama' => 'Kabupaten/Kota'],
        ]);

        // 2. m_jenis_satker (ID: Integer)
        DB::table('m_jenis_satker')->insert([
            ['nama' => 'Eselon I'],
            ['nama' => 'Eselon II'],
            ['nama' => 'Eselon III'],
            ['nama' => 'Eselon IV'],
            ['nama' => 'Eselon V'],
        ]);

        // 3. m_jenis_jabatan (ID: Integer)
        DB::table('m_jenis_jabatan')->insert([
            ['nama' => 'Struktural'],
            ['nama' => 'Fungsional'],
            ['nama' => 'Pelaksana'],
        ]);

        // 4. m_eselon_level (ID: Integer)
        DB::table('m_eselon_level')->insert([
            ['nama' => 'I'],
            ['nama' => 'II'],
            ['nama' => 'III'],
            ['nama' => 'IV'],
            ['nama' => 'V'],
        ]);

        // 5. m_jenis_penugasan (ID: Integer)
        DB::table('m_jenis_penugasan')->insert([
            ['nama' => 'Definitif'],
            ['nama' => 'Plt'],
            ['nama' => 'Plh'],
            ['nama' => 'Admin'],
        ]);

        
        $roles = [
            ['key' => 'super_admin', 'nama' => 'Super Admin'],
            ['key' => 'admin_pusat', 'nama' => 'Admin Pusat'],
            ['key' => 'admin_wilayah', 'nama' => 'Admin Wilayah'],
            ['key' => 'admin_satker', 'nama' => 'Admin Satker'],
            ['key' => 'viewer', 'nama' => 'Viewer'],
        ];

        foreach ($roles as $role) {
            DB::table('m_roles')->insert([
                'key' => $role['key'],
                'nama' => $role['nama'],
            ]);
        }
    }
}